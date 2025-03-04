<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MerchantApiLogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'merchant' => [
                'id' => $this->merchant->id,
                'name' => $this->merchant->name,
                'uuid' => $this->merchant->uuid,
            ],
            'order' => $this->when($this->order, function () {
                return [
                    'id' => $this->order->id,
                    'uuid' => $this->order->uuid,
                    'external_id' => $this->order->external_id,
                    'amount' => $this->order->amount->toBeauty(),
                    'currency' => $this->order->currency->getCode(),
                    'status' => $this->order->status->value,
                    'status_name' => $this->order->status->name,
                    'sub_status' => $this->order->sub_status->value,
                    'sub_status_name' => $this->order->sub_status->name,
                ];
            }),
            'external_id' => $this->external_id,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'payment_gateway' => $this->payment_gateway,
            'request_data' => $this->request_data,
            'response_data' => $this->response_data,
            'ip_address' => $this->ip_address,
            'user_agent' => $this->user_agent,
            'is_successful' => $this->is_successful,
            'error_message' => $this->error_message,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
