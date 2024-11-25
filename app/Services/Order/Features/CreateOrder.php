<?php

namespace App\Services\Order\Features;

use App\DTO\Order\OrderCreateDTO;
use App\Enums\OrderStatus;
use App\Enums\TransactionType;
use App\Exceptions\OrderException;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\PaymentDetail;
use App\Models\PaymentGateway;
use App\Services\Money\Currency;
use App\Services\Money\Money;
use App\Services\Order\Utils\ConversionPriceCalculator;
use App\Services\Order\Utils\PaymentDetailProvider;
use App\Services\Order\Utils\ProfitCalculator;
use App\Services\Order\Utils\ServiceCommission;
use App\Services\Order\Utils\TraderCommissionRate;
use App\Services\Order\ValueObjects\ServiceCommissionValue;
use Illuminate\Support\Carbon;
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
        $this->validateMerchant();
    }

    /**
     * @throws OrderException
     */
    public function handle(): Order
    {
        $paymentDetail = (new PaymentDetailProvider(
            amount: $this->dto->amount,
            paymentGatewayCode: $this->dto->paymentGatewayCode,
            paymentDetailType: $this->dto->paymentDetailType,
        ))->provide();

        $paymentGateway = $paymentDetail->paymentGateway;

        $serviceCommission = (new ServiceCommission($paymentGateway, $this->dto->merchant))->getCommissionRate();

        $amount = $this->getAmount($serviceCommission);

        $expiresAt = $this->getExpirationTime($paymentGateway);

        $traderCommissionRate = (new TraderCommissionRate($paymentGateway))->getCommissionRate();

        $conversionPrice = (new ConversionPriceCalculator(
            currency: $paymentGateway->currency,
            traderCommissionRate: $traderCommissionRate
        ))->calculate();

        $profit = (new ProfitCalculator(
            amount: $amount,
            conversionPrice: $conversionPrice,
            serviceCommission: $serviceCommission
        ))->calculate();

        services()->wallet()->takeTrust(
            wallet: $paymentDetail->user->wallet,
            amount: $profit->profit,
            type: TransactionType::PAYMENT_FOR_OPENED_ORDER
        );

        return Order::create([
            'uuid' => (string)Str::uuid(),
            'external_id' => $this->dto->external_id,
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
            'callback_url' => $this->dto->callback_url,
            'success_url' => $this->dto->success_url,
            'fail_url' => $this->dto->fail_url,
            'is_h2h' => $this->dto->h2h,
            'payment_gateway_id' => $paymentGateway->id,
            'payment_detail_id' => $paymentDetail->id,
            'currency_id' => $paymentGateway->currency_id,
            'expires_at' => $expiresAt,
        ]);
    }

    protected function getExpirationTime(PaymentGateway $paymentGateway): Carbon
    {
        return now()->addMinutes($paymentGateway->reservation_time);
    }

    protected function validateMerchant(): void
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
    }

    protected function getAmount(ServiceCommissionValue $serviceCommission): Money
    {
        $client_commission_amount = 0;
        if ($serviceCommission->serviceCommissionRateClient > 0) {
            $client_commission_amount = $this->dto
                ->amount
                ->mul($serviceCommission->serviceCommissionRateClient / 100);
        }
        return $this->dto->amount->add($client_commission_amount);
    }
}
