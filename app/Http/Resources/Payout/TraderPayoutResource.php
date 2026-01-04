<?php

namespace App\Http\Resources\Payout;

use App\Enums\PayoutMethodType;
use App\Enums\PayoutStatus;
use App\Models\Payout\Payout;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Payout
 */
class TraderPayoutResource extends JsonResource
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
            'uuid' => $this->uuid,
            'status' => $this->status->value,
            'status_label' => $this->statusLabel(),
            'payout_method_type' => [
                'value' => $this->payout_method_type->value,
                'label' => $this->methodTypeLabel(),
            ],
            'requisites' => $this->requisites,
            'initials' => $this->initials,
            'amount' => [
                'fiat' => $this->amount_fiat?->toBeauty(),
                'currency' => strtoupper($this->amount_fiat?->getCurrency()->getCode()),
            ],
            'usdt_body' => [
                'value' => $this->usdt_body?->toBeauty(),
                'currency' => strtoupper($this->usdt_body?->getCurrency()->getCode()),
            ],
            'trader_credit' => [
                'value' => $this->trader_credit?->toBeauty(),
                'currency' => strtoupper($this->trader_credit?->getCurrency()->getCode()),
            ],
            'commissions' => [
                'trader_fee' => $this->trader_fee?->toBeauty(),
                'trader_rate' => $this->trader_commission_rate,
            ],
            'payment_gateway' => [
                'id' => $this->payment_gateway_id,
                'name' => $this->paymentGateway?->name,
                'code' => $this->paymentGateway?->code,
                'logo' => $this->paymentGateway?->logo ? asset('storage/logos/'.$this->paymentGateway?->logo) : null,
                'currency' => strtoupper($this->paymentGateway?->currency->getCode()),
            ],
            'timings' => [
                'created_at' => $this->created_at?->toIso8601String(),
                'expires_at' => $this->expires_at?->toIso8601String(),
                'taken_at' => $this->taken_at?->toIso8601String(),
                'sent_at' => $this->sent_at?->toIso8601String(),
                'hold_until' => $this->hold_until?->toIso8601String(),
                'completed_at' => $this->completed_at?->toIso8601String(),
            ],
        ];
    }

    private function statusLabel(): string
    {
        return match ($this->status) {
            PayoutStatus::OPEN => 'Открыта',
            PayoutStatus::TAKEN => 'В работе',
            PayoutStatus::SENT => 'Отправлено',
            PayoutStatus::COMPLETED => 'Завершена',
            PayoutStatus::CANCELED => 'Отменена',
        };
    }

    private function methodTypeLabel(): string
    {
        return match ($this->payout_method_type) {
            PayoutMethodType::SBP => 'СБП',
            PayoutMethodType::CARD => 'Карта',
        };
    }
}


