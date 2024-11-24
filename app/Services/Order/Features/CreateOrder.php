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
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

//TODO добавить возможность создавать множественные ордера с одной суммой для одного и тогоже юзера
class CreateOrder extends BaseFeature
{
    public function __construct(
        protected OrderCreateDTO $dto
    )
    {}

    /**
     * @throws OrderException
     */
    public function handle(): Order
    {
        $merchant = queries()->merchant()->findByUUID($this->dto->merchant_uuid);

        $this->validateMerchant($merchant);
        
        /**
         * @var PaymentGateway $paymentGateway
         * @var PaymentDetail $paymentDetail
         */
        list($paymentGateway, $paymentDetail) = $this->getPaymentGatewayAndDetail();

        //

        $service_commissions = $merchant->user->meta->service_commissions;

        $service_commission_rate_total = $paymentGateway->service_commission_rate;
        $service_commission_rate_merchant = $paymentGateway->service_commission_rate;
        $service_commission_rate_client = 0;

        if (isset($service_commissions[$merchant->id][$paymentGateway->id])) {
            $service_commission_rate_merchant = $service_commissions[$merchant->id][$paymentGateway->id];
            $service_commission_rate_client = $service_commission_rate_total - $service_commission_rate_merchant;
        }

        $client_commission_amount = 0;
        if ($service_commission_rate_client > 0) {
            $client_commission_amount = $this->dto
                ->amount
                ->mul($service_commission_rate_client / 100);
        }
        $amount = $this->dto->amount->add($client_commission_amount);

        //

        $expires_at = $this->getExpirationTime($paymentGateway);

        $trader_commission_rate = $this->commissionRateBonus($paymentGateway->commission_rate);

        list($base_conversion_price, $conversion_price)
            = $this->calcConversionPrices($paymentGateway->currency, $trader_commission_rate);

        $profit = $amount->convert($conversion_price, Currency::USDT());
        $trader_profit = $amount
            ->convert($base_conversion_price, Currency::USDT())
            ->sub($profit);

        $service_profit = $profit->mul($service_commission_rate_total / 100);
        $merchant_profit = $profit->sub($service_profit);

        services()->wallet()->takeTrust(
            wallet: $paymentDetail->user->wallet,
            amount: $profit,
            type: TransactionType::PAYMENT_FOR_OPENED_ORDER
        );

        return Order::create([
            'uuid' => (string)Str::uuid(),
            'external_id' => $this->dto->external_id,
            'merchant_id' => $merchant->id,
            'base_amount' => $this->dto->amount,
            'amount' => $amount,
            'profit' => $profit,
            'trader_profit' => $trader_profit,
            'merchant_profit' => $merchant_profit,
            'service_profit' => $service_profit,
            'currency' => $paymentGateway->currency,
            'base_conversion_price' => $base_conversion_price,
            'conversion_price' => $conversion_price,
            'trader_commission_rate' => $trader_commission_rate,
            'service_commission_rate_total' => $service_commission_rate_total,
            'service_commission_rate_merchant' => $service_commission_rate_merchant,
            'service_commission_rate_client' => $service_commission_rate_client,
            'status' => OrderStatus::PENDING,
            'callback_url' => $this->dto->callback_url,
            'success_url' => $this->dto->success_url,
            'fail_url' => $this->dto->fail_url,
            'is_h2h' => $this->dto->h2h,
            'payment_gateway_id' => $paymentGateway->id,
            'payment_detail_id' => $paymentDetail->id,
            'currency_id' => $paymentGateway->currency_id,
            'expires_at' => $expires_at,
        ]);
    }

    /**
     * @throws OrderException
     */
    protected function getPaymentGatewayAndDetail(): array
    {
        if ($this->dto->payment_gateway) {
            $paymentGateways = queries()
                ->paymentGateway()
                ->getByCodeForOrderCreate($this->dto->payment_gateway, $this->dto->amount);
        } elseif ($this->dto->currency) {
            $paymentGateways = queries()
                ->paymentGateway()
                ->getByCurrencyForOrderCreate($this->dto->currency, $this->dto->amount);
        } else {
            throw OrderException::make('Требуется валюта или платежный метод.');
        }

        if ($paymentGateways->isEmpty()) {
            throw OrderException::make('Подходящий платежный метод не найден. Попробуйте изменить метод/валюту или сумму.');
        }

        $base_conversion_price = services()->market()->getBuyPrice(
            $this->dto->amount->getCurrency()
        );
        $amount_usdt = $this->dto->amount->convert($base_conversion_price, Currency::USDT());

        $paymentDetail = queries()
            ->paymentDetail()
            ->getForOrderCreate(
                amount: $this->dto->amount,
                amount_usdt: $amount_usdt,
                payment_gateway_ids: $paymentGateways->pluck('id')->toArray(),
                payment_detail_type: $this->dto->payment_detail_type
            );

        if (! $paymentDetail) {
            throw OrderException::make('Подходящие платежные реквизиты не найдены.');
        }

        return [$paymentDetail->paymentGateway, $paymentDetail];
    }

    protected function calcConversionPrices(Currency $currency, float $commission_rate): array
    {
        $base_conversion_price = services()->market()->getBuyPrice($currency);
        $commission_multiplier = $commission_rate / 100;
        $commission_part = $base_conversion_price->mul($commission_multiplier);
        $conversion_price = $base_conversion_price->add($commission_part);

        return [$base_conversion_price, $conversion_price];
    }

    protected function getExpirationTime(PaymentGateway $paymentGateway): Carbon
    {
        return now()->addMinutes($paymentGateway->reservation_time);
    }

    //TODO refactoring
    protected function commissionRateBonus(float $commission_rate): float
    {
        $primeTimeBonus = services()->settings()->getPrimeTimeBonus();
        $start = Carbon::createFromTimeString($primeTimeBonus->starts);
        $end = Carbon::createFromTimeString($primeTimeBonus->ends);

        if (now()->between($start, $end)) {
            return round($commission_rate + $primeTimeBonus->rate, 2);
        }

        return $commission_rate;
    }

    protected function validateMerchant(Merchant $merchant): void
    {
        if (!$merchant->validated_at) {
            throw new OrderException('Мерчант находится на модерации.');
        }
        if ($merchant->banned_at) {
            throw new OrderException('Мерчант заблокирован.');
        }
        if (!$merchant->active) {
            throw new OrderException('Мерчант отключен.');
        }
    }
}
