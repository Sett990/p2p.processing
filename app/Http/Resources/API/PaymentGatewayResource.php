<?php

namespace App\Http\Resources\API;

use App\Models\PaymentGateway;
use App\Services\Money\Currency;
use App\Services\Money\Money;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentGatewayResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $initialBuyPrice = services()->market()->getBuyPrice($this->currency);

        /**
         * @var PaymentGateway $this
         */
        return [
            'name' => $this->name,
            'code' => $this->code,
            'schema' => $this->schema,
            'sub_methods' => $this->when($this->sub_payment_gateways, function () {
                return $this->sub_payment_gateways->transform(function ($gateway) {
                    return [
                        'name' => $gateway->name,
                        'code' => $gateway->code,
                    ];
                });
            }),
            'currency' => $this->currency->getCode(),
            'min_limit' => $this->min_limit,
            'max_limit' => $this->max_limit,
            'reservation_time' => $this->reservation_time,
            'detail_types' => $this->detail_types,
            'order_service_commission_rate' => $this->order_service_commission_rate,//TODO deprecated in new api version
            'payout_service_commission_rate' => $this->payout_service_commission_rate,//TODO deprecated in new api version
            'buy_price_markup_rate' => $this->buy_price_markup_rate,//TODO deprecated in new api version
            'sell_price_markup_rate' => $this->sell_price_markup_rate,//TODO deprecated in new api version
            'base_conversion_price' => $initialBuyPrice->toPrecision(),//TODO deprecated in new api version
            'conversion_price' => $this->calcConversionPriceWithCommission($this->currency, $this->buy_price_markup_rate, $initialBuyPrice)->toPrecision(),//TODO deprecated in new api version
        ];
    }

    protected function calcConversionPriceWithCommission(Currency $currency, float $commissionRate, Money $baseConversionPrice): Money
    {
        $commissionMultiplier = $commissionRate / 100;
        $commissionPart = $baseConversionPrice->mul($commissionMultiplier);
        return $baseConversionPrice->add($commissionPart);
    }
}
