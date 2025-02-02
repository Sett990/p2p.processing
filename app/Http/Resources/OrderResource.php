<?php

namespace App\Http\Resources;

use App\Models\Order;
use App\Services\Money\Currency;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $items = explode('-', $this->uuid);
        $shotUUID = $items[count($items) - 1];
        /**
         * @var Order $this
         */
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'uuid_short' => $shotUUID,
            'external_id' => $this->external_id,
            'base_amount' => $this->amount->toBeauty(),
            'amount' => $this->amount->toBeauty(),
            'profit' => $this->profit->toBeauty(),
            'trader_profit' => $this->trader_profit->toBeauty(),
            'merchant_profit' => $this->merchant_profit->toBeauty(),
            'service_profit' => $this->service_profit->toBeauty(),
            'base_conversion_price' => $this->base_conversion_price->toBeauty(),
            'conversion_price' => $this->conversion_price->toBeauty(),
            'trader_commission_rate' => $this->trader_commission_rate,
            'service_commission_rate_total' => $this->service_commission_rate_total,
            'service_commission_rate_merchant' => $this->service_commission_rate_merchant,
            'service_commission_rate_client' => $this->service_commission_rate_client,
            'service_commission_amount_total' => (float)$this->profit
                ->mul($this->service_commission_rate_total / 100)
                ->toBeauty(),
            'currency' => $this->currency->getCode(),
            'base_currency' => Currency::USDT()->getCode(),
            'status' => $this->status->value,
            'status_name' => $this->status_name,
            'callback_url' => $this->callback_url,
            'is_h2h' => $this->is_h2h,
            $this->mergeWhen($this->resource->relationLoaded('paymentGateway'), function () {
                return [
                    'payment_gateway_code' => $this->paymentGateway?->code,
                    'payment_gateway_name' => $this->paymentGateway?->name_with_currency,
                ];
            }),
            $this->mergeWhen($this->resource->relationLoaded('paymentGateway'), function () {
                return [
                    'payment_gateway_code' => $this->paymentGateway->code,
                    'payment_gateway_name' => $this->paymentGateway->name_with_currency,
                ];
            }),
            $this->mergeWhen($this->resource->relationLoaded('paymentDetail'), function () {
                return [
                    'payment_detail' => $this->paymentDetail?->detail,
                    'payment_detail_type' => $this->paymentDetail?->detail_type->value,
                    'payment_detail_name' => $this->paymentDetail?->name,
                    'sub_payment_gateway_code' => optional($this->paymentDetail->subPaymentGateway)->code,
                    'sub_payment_gateway_name' => optional($this->paymentDetail->subPaymentGateway)->name,
                    'user' => UserResource::make($this->paymentDetail->user)
                ];
            }),
            $this->mergeWhen($this->resource->relationLoaded('smsLog') && $this->smsLog, function () {
                return [
                    'sms_log' => [
                        'sender' => $this->smsLog->sender,
                        'message' => $this->smsLog->message,
                        'created_at' => $this->smsLog->created_at->toDateTimeString(),
                    ]
                ];
            }),
            $this->mergeWhen($this->resource->relationLoaded('merchant'), function () {
                return [
                    'merchant' => [
                        'id' => $this->merchant->id,
                        'name' => $this->merchant->name,
                    ],
                ];
            }),
            $this->mergeWhen($this->resource->relationLoaded('dispute'), function () {
                return [
                    'has_dispute' => (bool)$this->dispute,
                ];
            }),
            'expires_at' => $this->expires_at?->toDateTimeString(),
            'finished_at' => $this->finished_at?->toDateTimeString(),
            'created_at' => $this->created_at->toDateTimeString(),
            'payment_link' => route('payment.show', $this->uuid),
        ];
    }
}
