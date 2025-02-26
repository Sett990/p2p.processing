<?php

namespace App\Services\Order\Features\OrderDetailProvider\Classes;

use App\Enums\DetailType;
use App\Enums\DisputeStatus;
use App\Enums\OrderStatus;
use App\Models\Dispute;
use App\Models\User;
use App\Services\Order\Features\OrderDetailProvider\OrderDetailProvider;
use App\Services\Order\Features\OrderDetailProvider\Values\Gateway;
use App\Services\Order\Features\OrderDetailProvider\Values\Trader;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use function Symfony\Component\Translation\t;

class TradersProvider
{
    public function __construct(
        protected ?DetailType $detailType = null,
    )
    {}

    /**
     * @param Collection<int, Gateway> $gateways
     * @return Collection<int, Trader>
     */
    public function get(Collection $gateways): Collection
    {
        $traders = collect();

        $pendingDisputesCount = Dispute::query()
            ->where('status', DisputeStatus::PENDING)
            ->select('trader_id', DB::raw('count(*) as disputes_count'))
            ->groupBy('trader_id')
            ->get()
            ->pluck('disputes_count', 'trader_id')
            ->toArray();

        $users = User::query()
            ->with(['wallet' => function (HasOne $query) {
                $query->select(['user_id', 'trust_balance', 'currency']);
            }])
            ->where('is_online', true)
            ->whereNull('banned_at')
            ->whereHas('paymentDetails', function ($query) use ($gateways) {
                $query->active();
                $query->whereIn('payment_gateway_id', $gateways->pluck('id')->toArray());
                $query->when($this->detailType, function (Builder $query) {
                    $query->where('detail_type', $this->detailType);
                });
            })
            ->select([
                'id'
            ])
            ->get();

        $maxPendingDisputes = services()->settings()->getMaxPendingDisputes();

        if ($maxPendingDisputes > 0) {
            $users = $users->filter(function (User $user) use ($maxPendingDisputes, $pendingDisputesCount) {
                $count = isset($pendingDisputesCount[$user->id]) ? $pendingDisputesCount[$user->id] : 0;

                return $count < $maxPendingDisputes;
            });
        }

        $users->each(function (User $user) use (&$traders) {
                $traders->push(
                    new Trader(
                        id: $user->id,
                        trustBalance: $user->wallet->trust_balance,
                    )
                );
            });

        return $traders;
    }
}
