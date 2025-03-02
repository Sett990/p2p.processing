<?php

namespace App\Http\Resources;

use App\Models\PaymentGateway;
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
        /**
         * @var PaymentGateway $this
         */
        return [
            'id' => $this->id,
            'name' => $this->name_with_currency,
            'original_name' => $this->name,
            'code' => $this->code,
            'nspk_schema' => $this->nspk_schema,
            'detail_types' => $this->detail_types,
            'sub_payment_gateways' => $this->sub_payment_gateways?->transform(function (PaymentGateway $gateway) {
                return [
                    'id' => $gateway->id,
                    'name' => $gateway->name_with_currency,
                    'original_name' => $gateway->name,
                    'code' => $gateway->code,
                    'currency' => $gateway->currency->getCode(),
                ];
            }),
            'sub_methods' => $this->sub_payment_gateways?->pluck('code')?->toArray() ?? [],
            'currency' => $this->currency->getCode(),
            'min_limit' => $this->min_limit,
            'max_limit' => $this->max_limit,
            'sms_senders' => $this->sms_senders,
            'trader_commission_rate_for_orders' => $this->trader_commission_rate_for_orders,
            'trader_commission_rate_for_payouts' => $this->trader_commission_rate_for_payouts,
            'total_service_commission_rate_for_orders' => $this->total_service_commission_rate_for_orders,
            'total_service_commission_rate_for_payouts' => $this->total_service_commission_rate_for_payouts,
            'is_active' => $this->is_active,
            'reservation_time' => $this->reservation_time,
            'payout_reservation_time' => $this->payout_reservation_time,
            'logo_path' => $this->logo ? asset('storage/logos/'.$this->logo) : null,
        ];
    }
}
