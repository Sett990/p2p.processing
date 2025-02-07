<?php

namespace App\Queries\Eloquent;

use App\Enums\DetailType;
use App\Enums\OrderStatus;
use App\Models\PaymentDetail;
use App\Models\User;
use App\ObjectValues\TableFilters\TableFiltersValue;
use App\Queries\Interfaces\PaymentDetailQueries;
use App\Services\Money\Money;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class PaymentDetailQueriesEloquent implements PaymentDetailQueries
{
    public function paginateForAdmin(TableFiltersValue $filters): LengthAwarePaginator
    {
        return PaymentDetail::query()
            ->with(['paymentGateway', 'user'])
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
            ->orderByDesc('id')
            ->paginate(10);
    }

    public function paginateForUser(User $user, TableFiltersValue $filters): LengthAwarePaginator
    {
        return PaymentDetail::query()
            ->where('user_id', $user->id)
            ->with(['paymentGateway'])
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

    public function getForOrderCreate(Money $amount, Money $amount_usdt, array $payment_gateway_ids, ?int $sub_payment_gateway_id, ?DetailType $payment_detail_type = null): ?PaymentDetail
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
            ->when($sub_payment_gateway_id, function (Builder $query) use ($sub_payment_gateway_id) {
                $query->where('sub_payment_gateway_id', $sub_payment_gateway_id);
            })
            ->active()
            ->whereRaw("daily_limit - current_daily_limit >= {$amount->toUnits()}")
            ->whereNotIn('user_id', $users_ids)
            ->inRandomOrder()
            ->first();
    }
}
