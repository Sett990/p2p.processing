<?php

namespace App\Queries\Eloquent;

use App\Enums\OrderStatus;
use App\Models\Dispute;
use App\Models\Order;
use App\Models\User;
use App\ObjectValues\TableFilters\TableFiltersValue;
use App\Queries\Interfaces\OrderQueries;
use App\Services\Money\Currency;
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

    public function paginateForAdmin(TableFiltersValue $filters): LengthAwarePaginator
    {
        return Order::query()
            ->with(['paymentDetail.subPaymentGateway', 'paymentGateway', 'smsLog', 'merchant', 'dispute', 'paymentDetail.user'])
            ->when(! empty($filters->orderStatuses), function ($query) use ($filters) {
                $query->whereIn('status', $filters->orderStatuses);
            })
            ->when($filters->dateRange->startDate, function ($query) use ($filters) {
                $query->whereDate('created_at', '>=', $filters->dateRange->startDate);
            })
            ->when($filters->dateRange->endDate, function ($query) use ($filters) {
                $query->whereDate('created_at', '<=', $filters->dateRange->endDate);
            })
            ->when($filters->externalID, function ($query) use ($filters) {
                $query->where('external_id', 'LIKE', '%' . $filters->externalID . '%');
            })
            ->when($filters->uuid, function ($query) use ($filters) {
                $query->where('uuid', 'LIKE', '%' . $filters->uuid . '%');
            })
            ->when($filters->amount, function ($query) use ($filters) {
                $query->where(function ($query) use ($filters) {
                    $amount = Money::fromPrecision($filters->amount, Currency::USDT())->toUnits();
                    $query->where('amount', 'LIKE', '%' . $amount . '%');
                    $query->orWhere('profit', 'LIKE', '%' . $amount . '%');
                });
            })
            ->when($filters->paymentDetail, function ($query) use ($filters) {
                $query->whereRelation('paymentDetail', 'detail', 'LIKE', '%' . $filters->paymentDetail . '%');
            })
            ->when($filters->user, function ($query) use ($filters) {
                $query->where(function ($query) use ($filters) {
                    $query->whereRelation('paymentDetail.user', 'name', 'LIKE', '%' . $filters->user . '%');
                    $query->orWhereRelation('paymentDetail.user', 'email', 'LIKE', '%' . $filters->user . '%');
                });
            })
            ->orderByDesc('id')
            ->paginate(10);
    }

    public function paginateForUser(User $user, TableFiltersValue $filters): LengthAwarePaginator
    {
        return Order::query()
            ->whereRelation('paymentDetail', 'user_id', $user->id)
            ->with(['paymentDetail.subPaymentGateway', 'paymentGateway', 'smsLog', 'dispute'])
            ->when(! empty($filters->orderStatuses), function ($query) use ($filters) {
                $query->whereIn('status', $filters->orderStatuses);
            })
            ->when($filters->dateRange->startDate, function ($query) use ($filters) {
                $query->whereDate('created_at', '>=', $filters->dateRange->startDate);
            })
            ->when($filters->dateRange->endDate, function ($query) use ($filters) {
                $query->whereDate('created_at', '<=', $filters->dateRange->endDate);
            })
            ->when($filters->uuid, function ($query) use ($filters) {
                $query->where('uuid', 'LIKE', '%' . $filters->uuid . '%');
            })
            ->orderByDesc('id')
            ->paginate(10);
    }

    public function paginateForMerchant(User $user, TableFiltersValue $filters): LengthAwarePaginator
    {
        return Order::query()
            ->withoutGlobalScopes()
            ->with(['merchant'])
            ->whereRelation('merchant', 'user_id', $user->id)
            ->when(! empty($filters->orderStatuses), function ($query) use ($filters) {
                $query->whereIn('status', $filters->orderStatuses);
            })
            ->when($filters->externalID, function ($query) use ($filters) {
                $query->where('external_id', 'LIKE', '%' . $filters->externalID . '%');
            })
            ->when($filters->uuid, function ($query) use ($filters) {
                $query->where('uuid', 'LIKE', '%' . $filters->uuid . '%');
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
