<?php

namespace App\Services\Order\Utils;

use App\Enums\DetailType;
use App\Exceptions\OrderException;
use App\Models\Merchant;
use App\Models\PaymentDetail;
use App\Models\PaymentGateway;
use App\Services\Money\Currency;
use App\Services\Money\Money;

class PaymentDetailProvider
{
    public function __construct(
        protected Merchant $merchant,
        protected Money $amount,
        protected ?string $paymentGatewayCode = null,
        protected ?string $subPaymentGatewayCode = null,
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

        /*//TODO refactoring
        if ($this->subPaymentGatewayCode) {
            $paymentGateways = $paymentGateways->filter(function (PaymentGateway $paymentGateway) {
                return $paymentGateway->sub_payment_gateways->pluck('code')->contains($this->subPaymentGatewayCode);
            });
        }*/

        $gatewaySettings = $this->merchant->gateway_settings;

        $paymentGateways = $paymentGateways->filter(function (PaymentGateway $paymentGateway) use ($gatewaySettings) {
            return isset($gatewaySettings[$paymentGateway->id]) && $gatewaySettings[$paymentGateway->id]['active'] === true;
        });

        if ($paymentGateways->isEmpty()) {
            throw OrderException::make('Подходящий платежный метод не найден. Попробуйте изменить метод/валюту или сумму.');
        }

        $subPaymentGateway = $this->subPaymentGatewayCode
            ? queries()->paymentGateway()->getByCode($this->subPaymentGatewayCode)
            : null;

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
                sub_payment_gateway_id: $subPaymentGateway?->id,
                payment_detail_type: $this->paymentDetailType
            );

        if (! $paymentDetail) {
            throw OrderException::make('Подходящие платежные реквизиты не найдены.');
        }

        return $paymentDetail;
    }
}
