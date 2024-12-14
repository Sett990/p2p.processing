<?php

namespace App\Http\Resources;

use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /**
         * @var Invoice $this
         */
        return [
            'id' => $this->id,
            'amount' => $this->amount->toBeauty(),
            'currency' => $this->currency->getCode(),
            'type' => $this->type->value,
            'balance_type' => $this->balance_type->value,
            'address' => $this->address,
            'status' => $this->status->value,
            'user' => [
               'id' => $this->wallet->user->id,
               'email' => $this->wallet->user->email,
            ],
            'wallet_id' => $this->wallet_id,
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
