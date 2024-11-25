<?php

namespace App\Services\Order\Utils;

use App\Enums\DetailType;
use App\Exceptions\OrderException;
use App\Models\PaymentDetail;
use App\Services\Money\Currency;
use App\Services\Money\Money;

class PaymentDetailProvider
{
    public function __construct(
        protected Money $amount,
        protected ?string $paymentGatewayCode = null,
        protected ?DetailType $paymentDetailType = null,
    )
    {}

    public function provide(): PaymentDetail
    {

        if ($this->paymentGatewayCode) {
            $paymentGateways = queries()
                ->paymentGateway()
                ->getByCodesForOrderCreate([$this->paymentGatewayCode], $this->amount);
        } else {
            $paymentGateways = queries()
                ->paymentGateway()
                ->getByCurrencyForOrderCreate($this->amount->getCurrency(), $this->amount);
        }

        if ($paymentGateways->isEmpty()) {
            throw OrderException::make('Подходящий платежный метод не найден. Попробуйте изменить метод/валюту или сумму.');
        }

        $conversionPrice = services()
            ->market()
            ->getBuyPrice($this->amount->getCurrency());
        $amountUSDT = $this->amount->convert($conversionPrice, Currency::USDT());

        $paymentDetail = queries()
            ->paymentDetail()
            ->getForOrderCreate(
                amount: $this->amount,
                amount_usdt: $amountUSDT,
                payment_gateway_ids: $paymentGateways->pluck('id')->toArray(),
                payment_detail_type: $this->paymentDetailType
            );

        if (! $paymentDetail) {
            throw OrderException::make('Подходящие платежные реквизиты не найдены.');
        }

        return $paymentDetail;
    }
}
