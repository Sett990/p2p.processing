<?php

namespace App\Http\Resources;

use App\Models\PaymentDetail;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /**
         * @var PaymentDetail $this
         */
        return [
            'id' => $this->id,
            'name' => $this->name,
            'detail' => $this->detail,
            'detail_type' => $this->detail_type->value,
            'initials' => $this->initials,
            'is_active' => $this->is_active,
            'daily_limit' => $this->daily_limit->toBeauty(),
            'current_daily_limit' => $this->current_daily_limit->toBeauty(),
            'pending_orders_count' => $this->pending_orders_count,
            'max_pending_orders_quantity' => $this->max_pending_orders_quantity,
            'min_order_amount' => $this->min_order_amount?->toBeauty(),
            'max_order_amount' => $this->max_order_amount?->toBeauty(),
            'order_interval_minutes' => $this->order_interval_minutes,
            'currency' => $this->currency->getCode(),
            'user_device_id' => $this->user_device_id,
            'created_at' => $this->created_at->toDateString(),
            $this->mergeWhen($this->resource->relationLoaded('user'), function () {
                return [
                    'owner_email' => $this->user->email,
                ];
            }),
            $this->mergeWhen($this->resource->relationLoaded('paymentGateway'), function () {
                return [
                    'payment_gateway_code' => $this->paymentGateway->code,
                    'payment_gateway_name' => $this->paymentGateway->name_with_currency,
                    'payment_gateway_logo_path' => $this->paymentGateway?->logo ? asset('storage/logos/'.$this->paymentGateway->logo) : null,
                ];
            }),
            $this->mergeWhen($this->resource->relationLoaded('userDevice'), function () {
                return [
                    'device_name' => $this->userDevice->name,
                    'device_model' => $this->userDevice->device_model,
                    'device_android_version' => $this->userDevice->android_version,
                ];
            }),
            'payment_gateway_ids' => $this->payment_gateway_ids ?? [],
        ];
    }
}
