<?php

namespace App\Http\Resources\API\Payout;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PayoutResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'payout_id' => $this->uuid,
            'status' => $this->status->value,
            'payout_method_type' => $this->payout_method_type->value,
            'requisites' => $this->requisites,
            'initials' => $this->initials,
            'merchant' => [
                'id' => $this->merchant?->uuid,
                'name' => $this->merchant?->name,
            ],
            'payment_gateway' => [
                'id' => $this->paymentGateway?->id,
                'name' => $this->paymentGateway?->name,
                'code' => $this->paymentGateway?->code,
            ],
            'amounts' => [
                'fiat' => [
                    'value' => $this->amount_fiat->toBeauty(),
                    'currency' => strtoupper($this->amount_fiat_currency),
                ],
                'usdt_body' => $this->usdt_body?->toBeauty(),
                'merchant_debit' => $this->merchant_debit?->toBeauty(),
                'trader_credit' => $this->trader_credit?->toBeauty(),
            ],
            'fees' => [
                'total' => $this->total_fee?->toBeauty(),
                'trader' => $this->trader_fee?->toBeauty(),
                'teamlead' => $this->teamlead_fee?->toBeauty(),
                'service' => $this->service_fee?->toBeauty(),
            ],
            'commissions' => [
                'total' => $this->total_commission_rate,
                'trader' => $this->trader_commission_rate,
                'teamlead' => $this->teamlead_commission_rate,
                'service' => $this->service_commission_rate,
            ],
            'rate' => [
                'market' => $this->rate_market->value,
                'price' => $this->conversion_price?->toBeauty(),
                'currency' => strtoupper($this->conversion_price_currency),
                'fixed_at' => $this->rate_fixed_at?->toIso8601String(),
            ],
            'timestamps' => [
                'taken_at' => $this->taken_at?->toIso8601String(),
                'sent_at' => $this->sent_at?->toIso8601String(),
                'hold_until' => $this->hold_until?->toIso8601String(),
                'completed_at' => $this->completed_at?->toIso8601String(),
                'canceled_at' => $this->canceled_at?->toIso8601String(),
                'created_at' => $this->created_at?->toIso8601String(),
            ],
            'calc_meta' => $this->calc_meta,
        ];
    }
}

