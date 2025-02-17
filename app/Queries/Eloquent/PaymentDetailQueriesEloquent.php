<?php

namespace App\Queries\Eloquent;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\PaymentDetail;
use App\Models\User;
use App\ObjectValues\TableFilters\TableFiltersValue;
use App\Queries\Interfaces\PaymentDetailQueries;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PaymentDetailQueriesEloquent implements PaymentDetailQueries
{
    public function paginateForAdmin(TableFiltersValue $filters): LengthAwarePaginator
    {
        return PaymentDetail::query()
            ->with(['paymentGateway', 'user'])
            ->withCount(['orders as pending_orders_count' => function ($query) {
                $query->where('status', OrderStatus::PENDING);
            }])
            ->when($filters->id, function ($query) use ($filters) {
                $query->where('id', $filters->id);
            })
            ->when($filters->name, function ($query) use ($filters) {
                $query->where('name', 'LIKE', '%' . $filters->name . '%');
            })
            ->when($filters->paymentDetail, function ($query) use ($filters) {
                $query->where('detail', 'LIKE', '%' . $filters->paymentDetail . '%');
            })
            ->when($filters->user, function ($query) use ($filters) {
                $query->whereRelation('user', 'name', 'LIKE', '%' . $filters->user . '%');
                $query->orWhereRelation('user', 'email', 'LIKE', '%' . $filters->user . '%');
            })
            ->when($filters->active, function ($query) use ($filters) {
                $query->where('is_active', true);
            })
            ->when($filters->multipliedDetails, function ($query) use ($filters) {
                $query->where('max_pending_orders_quantity', '>', 1);
            })
            ->when($filters->online, function ($query) use ($filters) {
                $query->whereRelation('user', 'is_online', true);
            })
            ->orderByDesc('id')
            ->paginate(10);
    }

    public function paginateForUser(User $user, TableFiltersValue $filters): LengthAwarePaginator
    {
        return PaymentDetail::query()
            ->where('user_id', $user->id)
            ->with(['paymentGateway'])
            ->withCount(['orders as pending_orders_count' => function ($query) {
                $query->where('status', OrderStatus::PENDING);
            }])
            ->when($filters->id, function ($query) use ($filters) {
                $query->where('id', $filters->id);
            })
            ->when($filters->name, function ($query) use ($filters) {
                $query->where('name', 'LIKE', '%' . $filters->name . '%');
            })
            ->when($filters->paymentDetail, function ($query) use ($filters) {
                $query->where('detail', 'LIKE', '%' . $filters->paymentDetail . '%');
            })
            ->when($filters->active, function ($query) use ($filters) {
                $query->where('is_active', true);
            })
            ->orderByDesc('id')
            ->paginate(10);
    }
}
