<?php

namespace App\Queries\Eloquent;

use App\Enums\OrderStatus;
use App\Models\Dispute;
use App\Models\Order;
use App\Models\User;
use App\Queries\Interfaces\OrderQueries;
use App\Services\Money\Money;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class OrderQueriesEloquent implements OrderQueries
{
    public function findPending(Money $amount, User $user): ?Order
    {
        return Order::where('amount', $amount->toUnits())
            ->where('status', OrderStatus::PENDING)
            ->where('currency', $amount->getCurrency()->getCode())
            ->whereRelation('paymentDetail', 'user_id', $user->id)
            ->first();
    }

    public function paginateForAdmin(): LengthAwarePaginator
    {
        return Order::query()
            ->with(['paymentDetail.subPaymentGateway', 'paymentGateway', 'smsLog', 'merchant', 'dispute'])
            ->orderByDesc('id')
            ->paginate(10);
    }

    public function paginateForUser(User $user): LengthAwarePaginator
    {
        return Order::query()
            ->whereRelation('paymentDetail', 'user_id', $user->id)
            ->with(['paymentDetail.subPaymentGateway', 'paymentGateway', 'smsLog', 'merchant', 'dispute'])
            ->orderByDesc('id')
            ->paginate(10);
    }

    public function paginateForMerchant(User $user): LengthAwarePaginator
    {
        return Order::query()
            ->withoutGlobalScopes()
            ->with(['paymentDetail.subPaymentGateway', 'paymentGateway', 'smsLog', 'merchant', 'dispute'])
            ->whereRelation('merchant', 'user_id', $user->id)
            ->orderByDesc('id')
            ->paginate(10);
    }


    /**
     * @return Collection<int, Dispute>
     */
    public function getForAdminApiDisputeCreate(): Collection
    {
        return Order::query()
            ->where('status', OrderStatus::FAIL)
            ->whereDoesntHave('dispute')
            ->whereDate('created_at', '>=', now()->subDay())
            ->orderByDesc('id')
            ->get(['id', 'amount', 'currency']);
    }
}
