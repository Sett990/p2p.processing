<?php

namespace App\Http\Resources;

use App\Models\Order;
use App\Services\Money\Currency;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TableOrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /**
         * @var Order $this
         */
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'amount' => $this->amount->toBeauty(),
            'total_profit' => $this->total_profit->toBeauty(),
            'currency' => $this->currency->getCode(),
            'base_currency' => Currency::USDT()->getCode(),
            'status' => $this->status->value,
            'status_name' => $this->status_name,
            'payment_gateway_name' => $this->paymentGateway->name,
            'payment_gateway_logo_path' => asset('storage/logos/'.$this->paymentGateway->logo),
            'payment_detail' => $this->paymentDetail->detail,
            'payment_detail_type' => $this->paymentDetail->detail_type->value,
            'payment_detail_name' => $this->paymentDetail->name,
            'device_name' => $this->paymentDetail->userDevice->name,
            'trader_email' => $this->trader->email,
            'trader_name' => $this->trader->name,
            'created_at' => $this->created_at->toISOString(),
        ];
    }
}
