<?php

namespace App\Http\Resources\API\Payout;

use App\Models\Payout\Payout;
use App\Services\Money\Money;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Payout
 */
class PayoutCallbackResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'payout_id' => $this->uuid,
            'external_id' => $this->external_id,
            'status' => $this->status->value,
            'payout_method_type' => $this->payout_method_type->value,
            'bank_name' => $this->bank_name,
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
                'fiat' => $this->formatMoney($this->amount_fiat, $this->amount_fiat_currency),
                'usdt_body' => $this->formatMoney($this->usdt_body, $this->usdt_body_currency),
                'merchant_debit' => $this->formatMoney($this->merchant_debit, $this->merchant_debit_currency),
                'trader_credit' => $this->formatMoney($this->trader_credit, $this->trader_credit_currency),
            ],
            'fees' => [
                'total' => $this->formatMoney($this->total_fee, $this->total_fee_currency),
                'trader' => $this->formatMoney($this->trader_fee, $this->trader_fee_currency),
                'teamlead' => $this->formatMoney($this->teamlead_fee, $this->teamlead_fee_currency),
                'service' => $this->formatMoney($this->service_fee, $this->service_fee_currency),
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
                'created_at' => $this->created_at?->toIso8601String(),
                'taken_at' => $this->taken_at?->toIso8601String(),
                'sent_at' => $this->sent_at?->toIso8601String(),
                'hold_until' => $this->hold_until?->toIso8601String(),
                'completed_at' => $this->completed_at?->toIso8601String(),
                'canceled_at' => $this->canceled_at?->toIso8601String(),
            ],
            'calc_meta' => $this->calc_meta,
        ];
    }

    private function formatMoney(?Money $money, ?string $currencyCode): ?array
    {
        if (! $money) {
            return null;
        }

        return [
            'value' => $money->toBeauty(),
            'currency' => strtoupper($currencyCode ?? $money->getCurrency()->getCode()),
        ];
    }
}



