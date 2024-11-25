<?php

namespace App\Queries\Eloquent;

use App\Enums\DetailType;
use App\Enums\OrderStatus;
use App\Models\PaymentDetail;
use App\Models\User;
use App\Queries\Interfaces\PaymentDetailQueries;
use App\Services\Money\Money;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class PaymentDetailQueriesEloquent implements PaymentDetailQueries
{
    public function paginateForAdmin(): LengthAwarePaginator
    {
        return PaymentDetail::query()
            ->with(['paymentGateway', 'user'])
            ->orderByDesc('id')
            ->paginate(10);
    }

    public function paginateForUser(User $user): LengthAwarePaginator
    {
        return PaymentDetail::query()
            ->where('user_id', $user->id)
            ->with(['paymentGateway'])
            ->orderByDesc('id')
            ->paginate(10);
    }

    public function getForOrderCreate(Money $amount, Money $amount_usdt, array $payment_gateway_ids, ?DetailType $payment_detail_type = null): ?PaymentDetail
    {
        $users_ids = PaymentDetail::whereHas('orders', function (Builder $query) use ($amount) {
            $query->where('status', OrderStatus::PENDING);
            $query->where('amount', $amount->toUnits());
        })
            ->select('user_id')
            ->pluck('user_id')
            ->toArray();

        $users_ids = array_unique($users_ids);

        return PaymentDetail::query()
            ->whereRelation('user', 'is_online', true)
            ->whereDoesntHave('orders', function (Builder $query) {
                $query->where('status', OrderStatus::PENDING);
            })
            ->whereHas('user.wallet', function (Builder $query) use ($amount_usdt) {
                $query->where('trust_balance', '>=', (int)$amount_usdt->toUnits());
            })
            ->when($payment_detail_type, function (Builder $query) use ($payment_detail_type) {
                $query->where('detail_type', $payment_detail_type);
            })
            ->whereIn('payment_gateway_id', $payment_gateway_ids)
            ->active()
            ->whereRaw("daily_limit - current_daily_limit >= {$amount->toUnits()}")
            ->whereNotIn('user_id', $users_ids)
            ->inRandomOrder()
            ->first();
    }
}
