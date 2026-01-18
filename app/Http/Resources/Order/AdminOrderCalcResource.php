<?php

namespace App\Http\Resources\Order;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Order
 */
class AdminOrderCalcResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $calcMeta = $this->calc_meta ?? [];
        if (! is_array($calcMeta)) {
            $calcMeta = [];
        }

        $inputs = $calcMeta['inputs'] ?? [];
        if (! array_key_exists('service_commission_rate', $inputs)) {
            $serviceRate = max(
                (float) $this->total_service_commission_rate
                - (float) $this->trader_commission_rate
                - (float) $this->team_leader_commission_rate,
                0
            );
            $inputs['service_commission_rate'] = $serviceRate;
        }
        $calcMeta['inputs'] = $inputs;

        $outputs = $calcMeta['outputs'] ?? [];
        if (! array_key_exists('trader_debit', $outputs) && $this->trader_paid_for_order) {
            $outputs['trader_debit'] = $this->trader_paid_for_order->toPrecision();
        }
        $calcMeta['outputs'] = $outputs;

        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'uuid_short' => mb_substr($this->uuid, 0, 8),
            'calc_meta' => $calcMeta,
        ];
    }
}
