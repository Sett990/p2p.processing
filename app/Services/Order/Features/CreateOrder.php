<?php

namespace App\Services\Order\Features;

use App\DTO\Order\OrderCreateDTO;
use App\Enums\OrderStatus;
use App\Enums\TransactionType;
use App\Exceptions\OrderException;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\PaymentGateway;
use App\Services\Money\Currency;
use App\Services\Money\Money;
use App\Services\Order\Utils\ConversionPriceCalculator;
use App\Services\Order\Utils\DailyLimit;
use App\Services\Order\Utils\PaymentAmountCalculator;
use App\Services\Order\Utils\PaymentDetailProvider;
use App\Services\Order\Utils\ProfitCalculator;
use App\Services\Order\Utils\ServiceCommission;
use App\Services\Order\Utils\TraderCommissionRate;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

//TODO добавить возможность создавать множественные ордера с одной суммой для одного и тогоже юзера
class CreateOrder extends BaseFeature
{
    protected readonly Merchant $merchant;

    /**
     * @throws OrderException
     */
    public function __construct(
        protected OrderCreateDTO $dto
    )
    {
        $this->validate();
    }

    /**
     * @throws OrderException
     */
    public function handle(): Order
    {
        if ($this->dto->manually) {
            return Order::create([
                'uuid' => (string)Str::uuid(),
                'external_id' => $this->dto->externalID,
                'merchant_id' => $this->dto->merchant->id,
                'base_amount' => $this->dto->amount,
                'amount' => $this->dto->amount,
                'profit' => Money::fromPrecision(0, Currency::USDT()),
                'trader_profit' => Money::fromPrecision(0, Currency::USDT()),
                'merchant_profit' => Money::fromPrecision(0, Currency::USDT()),
                'service_profit' => Money::fromPrecision(0, Currency::USDT()),
                'currency' => $this->dto->amount->getCurrency(),
                'base_conversion_price' => Money::fromPrecision(0, $this->dto->amount->getCurrency()),
                'conversion_price' => Money::fromPrecision(0, $this->dto->amount->getCurrency()),
                'trader_commission_rate' => 0,
                'service_commission_rate_total' => 0,
                'service_commission_rate_merchant' => 0,
                'service_commission_rate_client' => 0,
                'status' => OrderStatus::PENDING,
                'callback_url' => $this->dto->callbackURL,
                'success_url' => $this->dto->successURL,
                'fail_url' => $this->dto->failURL,
                'is_h2h' => false,
                'is_manually' => true,
                'payment_gateway_id' => null,
                'payment_detail_id' => null,
                'expires_at' => null,
            ]);
        }

        $paymentDetail = (new PaymentDetailProvider(
            amount: $this->dto->amount,
            paymentGatewayCode: $this->dto->paymentGatewayCode,
            subPaymentGatewayCode: $this->dto->subPaymentGatewayCode,
            paymentDetailType: $this->dto->paymentDetailType,
        ))->provide();

        $paymentGateway = $paymentDetail->paymentGateway;

        $expiresAt = $this->getExpirationTime($paymentGateway);

        $serviceCommission = (new ServiceCommission($paymentGateway, $this->dto->merchant))->getCommissionRate();
        $traderCommissionRate = (new TraderCommissionRate($paymentGateway))->getCommissionRate();

        $amount = (new PaymentAmountCalculator(
            amount: $this->dto->amount,
            serviceCommission: $serviceCommission
        ))->calculate();

        $conversionPrice = (new ConversionPriceCalculator(
            currency: $paymentGateway->currency,
            traderCommissionRate: $traderCommissionRate
        ))->calculate();

        $profit = (new ProfitCalculator(
            amount: $amount,
            conversionPrice: $conversionPrice,
            serviceCommission: $serviceCommission
        ))->calculate();

        return DB::transaction(function () use ($amount, $paymentDetail, $profit, $paymentGateway, $traderCommissionRate, $conversionPrice, $serviceCommission, $expiresAt) {
            (new DailyLimit(
                paymentDetail: $paymentDetail,
                amount: $amount
            ))->increment();

            services()->wallet()->takeTrust(
                wallet: $paymentDetail->user->wallet,
                amount: $profit->profit,
                type: TransactionType::PAYMENT_FOR_OPENED_ORDER
            );

            return Order::create([
                'uuid' => (string)Str::uuid(),
                'external_id' => $this->dto->externalID,
                'merchant_id' => $this->dto->merchant->id,
                'base_amount' => $this->dto->amount,
                'amount' => $amount,
                'profit' => $profit->profit,
                'trader_profit' => $profit->traderProfit,
                'merchant_profit' => $profit->merchantProfit,
                'service_profit' => $profit->serviceProfit,
                'currency' => $paymentGateway->currency,
                'base_conversion_price' => $conversionPrice->basePrice,
                'conversion_price' => $conversionPrice->actualPrice,
                'trader_commission_rate' => $traderCommissionRate,
                'service_commission_rate_total' => $serviceCommission->serviceCommissionRateTotal,
                'service_commission_rate_merchant' => $serviceCommission->serviceCommissionRateMerchant,
                'service_commission_rate_client' => $serviceCommission->serviceCommissionRateClient,
                'status' => OrderStatus::PENDING,
                'callback_url' => $this->dto->callbackURL,
                'success_url' => $this->dto->successURL,
                'fail_url' => $this->dto->failURL,
                'is_h2h' => $this->dto->h2h,
                'is_manually' => false,
                'payment_gateway_id' => $paymentGateway->id,
                'payment_detail_id' => $paymentDetail->id,
                'expires_at' => $expiresAt,
            ]);
        });
    }

    public function setPaymentDetail(Order $order): Order
    {
        if ($order->status->notEquals(OrderStatus::PENDING)) {
            throw new OrderException('Сделка была закрыта.');
        }

        $paymentDetail = (new PaymentDetailProvider(
            amount: $this->dto->amount,
            paymentGatewayCode: $this->dto->paymentGatewayCode,
            subPaymentGatewayCode: $this->dto->subPaymentGatewayCode,
            paymentDetailType: $this->dto->paymentDetailType,
        ))->provide();

        $paymentGateway = $paymentDetail->paymentGateway;

        $expiresAt = $this->getExpirationTime($paymentGateway);

        $serviceCommission = (new ServiceCommission($paymentGateway, $this->dto->merchant))->getCommissionRate();
        $traderCommissionRate = (new TraderCommissionRate($paymentGateway))->getCommissionRate();

        $amount = (new PaymentAmountCalculator(
            amount: $this->dto->amount,
            serviceCommission: $serviceCommission
        ))->calculate();

        $conversionPrice = (new ConversionPriceCalculator(
            currency: $paymentGateway->currency,
            traderCommissionRate: $traderCommissionRate
        ))->calculate();

        $profit = (new ProfitCalculator(
            amount: $amount,
            conversionPrice: $conversionPrice,
            serviceCommission: $serviceCommission
        ))->calculate();

        DB::transaction(function () use ($order, $amount, $paymentDetail, $profit, $paymentGateway, $traderCommissionRate, $conversionPrice, $serviceCommission, $expiresAt) {
            (new DailyLimit(
                paymentDetail: $paymentDetail,
                amount: $amount
            ))->increment();

            services()->wallet()->takeTrust(
                wallet: $paymentDetail->user->wallet,
                amount: $profit->profit,
                type: TransactionType::PAYMENT_FOR_OPENED_ORDER
            );

            $order->update([
                'amount' => $amount,
                'profit' => $profit->profit,
                'trader_profit' => $profit->traderProfit,
                'merchant_profit' => $profit->merchantProfit,
                'service_profit' => $profit->serviceProfit,
                'base_conversion_price' => $conversionPrice->basePrice,
                'conversion_price' => $conversionPrice->actualPrice,
                'trader_commission_rate' => $traderCommissionRate,
                'service_commission_rate_total' => $serviceCommission->serviceCommissionRateTotal,
                'service_commission_rate_merchant' => $serviceCommission->serviceCommissionRateMerchant,
                'service_commission_rate_client' => $serviceCommission->serviceCommissionRateClient,
                'payment_gateway_id' => $paymentGateway->id,
                'payment_detail_id' => $paymentDetail->id,
                'expires_at' => $expiresAt,
            ]);
        });

        return $order;
    }

    protected function getExpirationTime(PaymentGateway $paymentGateway): Carbon
    {
        return now()->addMinutes($paymentGateway->reservation_time);
    }

    protected function validate(): void
    {
        if (!$this->dto->merchant->validated_at) {
            throw new OrderException('Мерчант находится на модерации.');
        }
        if ($this->dto->merchant->banned_at) {
            throw new OrderException('Мерчант заблокирован.');
        }
        if (!$this->dto->merchant->active) {
            throw new OrderException('Мерчант отключен.');
        }
        if ($this->dto->h2h && $this->dto->successURL) {
            throw new OrderException('Для H2H сделок невозможно указать success url.');
        }
        if ($this->dto->h2h && $this->dto->failURL) {
            throw new OrderException('Для H2H сделок невозможно указать fail url.');
        }
    }
}
