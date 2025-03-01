<?php

namespace App\Services\Order\Features\OrderDetailProvider\Filters;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Services\Money\Money;
use App\Services\Order\Features\OrderDetailProvider\Values\Detail;
use Illuminate\Database\Eloquent\Collection;

class UniqueAmountByLatestFinishedOrders extends BaseFilter
{
    protected Collection $orders;

    public function __construct(
        protected Money $amount,
    )
    {
        $this->orders = Order::query()
            ->where('status', OrderStatus::SUCCESS)
            ->where('finished_at', '>=', now()->subMinutes(10))
            ->where('currency', $amount->getCurrency()->getCode())
            ->where(function ($query) {
                $query->where('amount', '>=', $this->amount->mul(0.8)->toUnits());
                $query->orWhere('amount', '<=', $this->amount->mul(1.2)->toUnits());
            })
            ->with(['paymentDetail' => function ($query) {
                $query->select('id', 'user_id');
            }])
            ->get(['id', 'amount', 'payment_detail_id', 'payment_gateway_id', 'currency', 'finished_at']);
    }

    public function check(Detail $detail): bool
    {
        $unique = ! $this->orders
            ->where('payment_gateway_id', $detail->gateway->id)
            ->where('amount', $detail->amount->toUnits())
            ->filter(function (Order $order) use ($detail) {
                return $order->paymentDetail->user_id === $detail->trader->id;
            })
            ->count();

        if (! $unique) {
            return false;
        }

        return true;
    }
}
