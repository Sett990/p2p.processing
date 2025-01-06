<?php

namespace App\Http\Resources;

use App\Models\User;
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
            'banned_at' => $this->banned_at?->toDateString(),
            'created_at' => $this->created_at->toDateString(),
            $this->mergeWhen($this->resource->relationLoaded('roles'), function () {
                return [
                    'role' => RoleResource::make($this->roles[0])->resolve()
                ];
            }),
            'meta' => $this->mergeWhen($this->resource->relationLoaded('meta'), function () {
                return [
                    'order_service_commission_rate' => $this->meta->order_service_commission_rate,
                    'payout_service_commission_rate' => $this->meta->payout_service_commission_rate,
                ];
            }),
            'order_service_commission_rate' => $this->when($this->meta, function () {
                return $this->meta->order_service_commission_rate;
            }),
            'payout_service_commission_rate' => $this->when($this->meta, function () {
                return $this->meta->payout_service_commission_rate;
            }),
            'payouts_enabled' => $this->payouts_enabled
        ];
    }
}
