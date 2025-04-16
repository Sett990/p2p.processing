<?php

namespace App\Services\Order\Features\OrderDetailProvider\Classes;

use App\Enums\DetailType;
use App\Enums\DisputeStatus;
use App\Enums\MarketEnum;
use App\Models\Merchant;
use App\Models\User;
use App\Services\Money\Currency;
use App\Services\Money\Money;
use App\Services\Order\Features\OrderDetailProvider\Values\Gateway;
use App\Services\Order\Features\OrderDetailProvider\Values\Trader;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;
use App\Services\Order\Features\OrderDetailProvider\Classes\Utils\TraderFactory;

class TradersProvider
{
    protected Money $approximateTotalProfit;

    public function __construct(
        protected Merchant $merchant,
        protected Money $amount,
        protected MarketEnum $market,
        protected ?DetailType $detailType = null,
    )
    {
        $exchangePrice = services()->market()->getBuyPrice($this->amount->getCurrency(), $this->market);
        $this->approximateTotalProfit = $amount->convert($exchangePrice, Currency::USDT());
    }

    /**
     * @param Collection<int, Gateway> $gateways
     * @return Collection<int, Trader>
     */
    public function get(Collection $gateways): Collection
    {
        $traders = collect();

        $maxPendingDisputes = services()->settings()->getMaxPendingDisputes();

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
                $query->where('trust_balance', '>=', $this->approximateTotalProfit->toUnitsInt());
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
            ->withCount(['disputes as pending_disputes_count' => function ($query) {
                $query->where('status', DisputeStatus::PENDING);
            }])
            ->when($maxPendingDisputes > 0, function ($query) use ($maxPendingDisputes) {
                $query->having('pending_disputes_count', '<', $maxPendingDisputes);
            })
            ->select([
                'id', 'promo_code_id'
            ])
            ->get();

        $users->each(function (User $user) use (&$traders) {
            $traders->push(
                TraderFactory::fromUser($user)
            );
        });

        return $traders;
    }
}
