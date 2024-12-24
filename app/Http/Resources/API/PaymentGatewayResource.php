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
        $conversionPrice = services()->market()->getBuyPrice($this->currency);

        /**
         * @var PaymentGateway $this
         */
        return [
            'name' => $this->name,
            'code' => $this->code,
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
            'order_service_commission_rate' => $this->order_service_commission_rate,
            'payout_service_commission_rate' => $this->payout_service_commission_rate,
            'buy_price_markup_rate' => $this->buy_price_markup_rate,
            'sell_price_markup_rate' => $this->sell_price_markup_rate,
            'base_conversion_price' => $conversionPrice->toPrecision(),
            'conversion_price' => $this->calcConversionPriceWithCommission($this->currency, $this->buy_price_markup_rate, $conversionPrice)->toPrecision(),
        ];
    }

    protected function calcConversionPriceWithCommission(Currency $currency, float $commissionRate, Money $baseConversionPrice): Money
    {
        $commissionMultiplier = $commissionRate / 100;
        $commissionPart = $baseConversionPrice->mul($commissionMultiplier);
        return $baseConversionPrice->add($commissionPart);
    }
}
