<?php

namespace App\Services\Order\Features\OrderDetailProvider\Classes;

use App\Enums\DetailType;
use App\Enums\DisputeStatus;
use App\Enums\MarketEnum;
use App\Models\Dispute;
use App\Models\Merchant;
use App\Models\User;
use App\Services\Money\Currency;
use App\Services\Money\Money;
use App\Services\Order\Features\OrderDetailProvider\Values\Gateway;
use App\Services\Order\Features\OrderDetailProvider\Values\Trader;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TradersProvider
{
    public function __construct(
        protected Merchant $merchant,
        protected MarketEnum $market,
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
            ->with(['meta' => function (HasOne $query) {
                $query->select(['allowed_markets', 'allowed_categories', 'user_id']);
            }])
            ->with([
                'promoCode:id,team_leader_id',
                'promoCode.teamLeader:id,referral_commission_percentage'
            ])
            ->where('is_online', true)
            ->where('stop_traffic', false)
            ->whereNull('banned_at')
            ->whereHas('wallet', function ($query) use ($gateways) {
                $query->where('trust_balance', '>=', Money::fromPrecision(10, Currency::USDT())->toUnitsInt());
            })
            ->whereHas('paymentDetails', function ($query) use ($gateways) {
                $query->active()
                    ->whereHas('paymentGateways', function ($query) use ($gateways) {
                        $query->whereIn('payment_gateways.id', $gateways->pluck('id'));
                    })
                    ->when($this->detailType, function (Builder $query) {
                        $query->where('detail_type', $this->detailType);
                    });
            })
            ->select([
                'id', 'promo_code_id'
            ])
            ->get();

        // Получаем ID категорий текущего мерчанта
        $merchantCategoryIds = $this->getMerchantCategoryIds();

        $users = $users->filter(function (User $user) use ($merchantCategoryIds) {
            // Проверяем разрешенные источники курса
            if (!empty($user->meta->allowed_markets) && !in_array($this->market->value, $user->meta->allowed_markets)) {
                return false;
            }

            // Проверяем разрешенные категории мерчантов
            if (!empty($user->meta->allowed_categories) && !empty($merchantCategoryIds)) {
                // Проверяем, есть ли пересечение между категориями мерчанта и разрешенными категориями трейдера
                $intersection = array_intersect($merchantCategoryIds, $user->meta->allowed_categories);
                if (empty($intersection)) {
                    return false;
                }
            }

            return true;
        });

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
                    teamLeaderID: $user->promoCode?->teamLeader?->id,
                    teamLeaderCommissionRate: $user->promoCode?->teamLeader?->referral_commission_percentage ?? 0,
                )
            );
        });

        return $traders;
    }

    /**
     * Получает ID категорий текущего мерчанта
     */
    protected function getMerchantCategoryIds(): array
    {
        if (!isset($this->merchant)) {
            return [];
        }

        return cache()->remember(
            'merchant_categories_' . $this->merchant->id,
            180,
            fn() => $this->merchant->categories()->pluck('categories.id')->toArray()
        );
    }
}
