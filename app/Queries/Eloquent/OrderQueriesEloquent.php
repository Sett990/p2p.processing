<?php

namespace App\Queries\Eloquent;

use App\Enums\OrderStatus;
use App\Models\Dispute;
use App\Models\Order;
use App\Models\User;
use App\Queries\Interfaces\OrderQueries;
use App\Services\Money\Money;
use Carbon\Carbon;
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

    public function paginateForAdmin(array $statuses = [], ?Carbon $startDate = null, ?Carbon $endDate = null, ?string $externalID = null, ?string $uuid = null): LengthAwarePaginator
    {
        return Order::query()
            ->with(['paymentDetail.subPaymentGateway', 'paymentGateway', 'smsLog', 'merchant', 'dispute', 'paymentDetail.user'])
            ->when(! empty($statuses), function ($query) use ($statuses) {
                $query->whereIn('status', $statuses);
            })
            ->when($startDate, function ($query) use ($startDate) {
                $query->whereDate('created_at', '>=', $startDate);
            })
            ->when($endDate, function ($query) use ($endDate) {
                $query->whereDate('created_at', '<=', $endDate);
            })
            ->when($externalID, function ($query) use ($externalID) {
                $query->where('external_id', 'LIKE', '%' . $externalID . '%');
            })
            ->when($uuid, function ($query) use ($uuid) {
                $query->where('uuid', 'LIKE', '%' . $uuid . '%');
            })
            ->orderByDesc('id')
            ->paginate(10);
    }

    public function paginateForUser(User $user, array $statuses = [], ?Carbon $startDate = null, ?Carbon $endDate = null, ?string $uuid = null): LengthAwarePaginator
    {
        return Order::query()
            ->whereRelation('paymentDetail', 'user_id', $user->id)
            ->with(['paymentDetail.subPaymentGateway', 'paymentGateway', 'smsLog', 'dispute'])
            ->when(! empty($statuses), function ($query) use ($statuses) {
                $query->whereIn('status', $statuses);
            })
            ->when($startDate, function ($query) use ($startDate) {
                $query->whereDate('created_at', '>=', $startDate);
            })
            ->when($endDate, function ($query) use ($endDate) {
                $query->whereDate('created_at', '<=', $endDate);
            })
            ->when($uuid, function ($query) use ($uuid) {
                $query->where('uuid', 'LIKE', '%' . $uuid . '%');
            })
            ->orderByDesc('id')
            ->paginate(10);
    }

    public function paginateForMerchant(User $user, array $statuses = [], ?string $externalID = null, ?string $uuid = null): LengthAwarePaginator
    {
        return Order::query()
            ->withoutGlobalScopes()
            ->with(['merchant'])
            ->whereRelation('merchant', 'user_id', $user->id)
            ->when(! empty($statuses), function ($query) use ($statuses) {
                $query->whereIn('status', $statuses);
            })
            ->when($externalID, function ($query) use ($externalID) {
                $query->where('external_id', 'LIKE', '%' . $externalID . '%');
            })
            ->when($uuid, function ($query) use ($uuid) {
                $query->where('uuid', 'LIKE', '%' . $uuid . '%');
            })
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
