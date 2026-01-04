<?php

namespace App\Queries\Eloquent;

use App\Enums\PayoutStatus;
use App\Models\Payout\Payout;
use App\Models\User;
use App\Queries\Interfaces\PayoutQueries;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class PayoutQueriesEloquent implements PayoutQueries
{
    private const ACTIVE_STATUSES = [
        PayoutStatus::TAKEN,
        PayoutStatus::SENT,
    ];

    /**
     * @inheritDoc
     */
    public function getStackForTrader(): Collection
    {
        return $this->baseQuery()
            ->where('status', PayoutStatus::OPEN->value)
            ->orderByDesc('id')
            ->get();
    }

    /**
     * @inheritDoc
     */
    public function getActiveForTrader(User $trader): Collection
    {
        return $this->baseQuery()
            ->where('trader_id', $trader->id)
            ->whereIn('status', $this->statusValues(self::ACTIVE_STATUSES))
            ->orderByRaw('CASE WHEN status = ? THEN 0 ELSE 1 END', [PayoutStatus::TAKEN->value])
            ->orderBy('taken_at')
            ->orderByDesc('id')
            ->get();
    }

    /**
     * @inheritDoc
     */
    public function paginateHistoryForTrader(User $trader, int $perPage = 15): LengthAwarePaginator
    {
        return $this->baseQuery()
            ->where('trader_id', $trader->id)
            ->whereIn('status', $this->statusValues([
                PayoutStatus::COMPLETED,
                PayoutStatus::CANCELED,
            ]))
            ->orderByDesc('id')
            ->paginate($perPage, ['*'], 'history_page');
    }

    public function countActiveForTrader(User $trader): int
    {
        return Payout::query()
            ->where('trader_id', $trader->id)
            ->whereIn('status', $this->statusValues(self::ACTIVE_STATUSES))
            ->count();
    }

    private function baseQuery(): Builder
    {
        return Payout::query()
            ->select([
                'id',
                'uuid',
                'merchant_id',
                'trader_id',
                'payment_gateway_id',
                'payout_method_type',
                'requisites',
                'initials',
                'amount_fiat',
                'amount_fiat_currency',
                'usdt_body',
                'usdt_body_currency',
                'merchant_debit',
                'merchant_debit_currency',
                'trader_credit',
                'trader_credit_currency',
                'total_fee',
                'total_fee_currency',
                'trader_fee',
                'trader_fee_currency',
                'teamlead_fee',
                'teamlead_fee_currency',
                'service_fee',
                'service_fee_currency',
                'rate_market',
                'conversion_price',
                'conversion_price_currency',
                'status',
                'taken_at',
                'sent_at',
                'hold_until',
                'completed_at',
                'canceled_at',
                'calc_meta',
                'created_at',
                'updated_at',
            ])
            ->with([
                'paymentGateway:id,name,code,logo,currency,reservation_time_for_payouts,trader_commission_rate_for_payouts,total_service_commission_rate_for_payouts',
                'merchant:id,name',
                'trader:id,name,email,payout_hold_enabled,payout_hold_minutes,payout_active_payouts_limit',
            ]);
    }

    /**
     * @param array<int, PayoutStatus> $statuses
     * @return array<int, string>
     */
    private function statusValues(array $statuses): array
    {
        return array_map(static fn (PayoutStatus $status) => $status->value, $statuses);
    }
}


