<?php

namespace App\Services\Order\Features\OrderDetailProvider\Filters;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Services\Money\Money;
use App\Services\Order\Features\OrderDetailProvider\Values\Detail;
use App\Services\Order\Utils\LatestFinishedOrders;
use Illuminate\Support\Collection;

class UniqueAmountByLatestFinishedOrders extends BaseFilter
{
    protected Collection $orders;
    protected string $min;
    protected string $max;

    public function __construct(
        protected Money $amount,
    )
    {
        $this->orders = collect(LatestFinishedOrders::get());

        $this->min = $this->amount->mul(0.9)->toUnits();
        $this->max = $this->amount->mul(1.1)->toUnits();

        $ordersIDs = Order::query()
            ->whereIn('id', $this->orders->pluck('id'))
            ->where('status', OrderStatus::SUCCESS)
            ->get(['id'])
            ->pluck('id')
            ->toArray();

        $this->orders = $this->orders->filter(function (array $order) use ($ordersIDs) {
            return in_array($order['id'], $ordersIDs)
                && $order['amount'] >= $this->min
                && $order['amount'] <= $this->max;
        });
    }

    public function check(Detail $detail): bool
    {
        $this->orders = $this->orders->filter(function (array $order) use ($detail) {
            return $order['payment_gateway_id'] === $detail->gateway->id
                && $order['user_device_id'] === $detail->userDeviceID;
        });

        $unique = ! $this->orders->count();

        if (! $unique) {
            return false;
        }

        return true;
    }
}
