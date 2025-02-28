<?php

namespace App\Http\Resources;

use App\Models\User;
use App\Services\Money\Currency;
use App\Services\Money\Money;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /**
         * @var User $this
         */
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'avatar_uuid' => $this->avatar_uuid,
            'avatar_style' => $this->avatar_style,
            'apk_latest_ping_at' => cache()->get("user-apk-latest-ping-at-$this->id"),
            'banned_at' => $this->banned_at?->toDateString(),
            'created_at' => $this->created_at->toDateString(),
            $this->mergeWhen($this->resource->relationLoaded('roles'), function () {
                return [
                    'role' => RoleResource::make($this->roles[0])->resolve()
                ];
            }),
            $this->mergeWhen($this->resource->relationLoaded('wallet'), function () {
                $amount = Money::fromPrecision(0, Currency::USDT());
                if ($this->hasRole('Merchant')) {
                    $amount = $this->wallet->merchant_balance;
                } else if ($this->hasRole('Trader')) {
                    $amount = $this->wallet->trust_balance;
                }

                return [
                    'balance' => $amount->toBeauty(),
                ];
            }),
            'payouts_enabled' => $this->payouts_enabled,
            'is_online' => $this->is_online,
            'is_payout_online' => $this->is_payout_online,
            'can_be_impersonated' => $this->id !== auth()->user()?->id,
        ];
    }
}
