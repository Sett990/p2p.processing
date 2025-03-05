<?php

namespace App\Services\Order\Features;

use App\Enums\OrderStatus;
use App\Enums\OrderSubStatus;
use App\Events\OrderReopenedFromFailedEvent;
use App\Events\OrderReopenedFromSucessfulEvent;
use App\Events\OrderFinishedAsFailedEvent;
use App\Events\OrderFinishedAsSuccessfulEvent;
use App\Exceptions\OrderException;
use App\Models\Order;

class OrderOperator
{
    public function __construct(
        protected int $orderID
    )
    {}

    public function finishOrderAsSuccessful(OrderSubStatus $subStatus): void
    {
        $order = Order::where('id', $this->orderID)->lockForUpdate()->first();

        if ($order->status->notEquals(OrderStatus::PENDING)) {
            throw OrderException::orderAlreadyFinished($order);
        }

        $order->update([
            'status' => OrderStatus::SUCCESS,
            'sub_status' => $subStatus,
            'finished_at' => now()
        ]);

        OrderFinishedAsSuccessfulEvent::dispatch($order);
    }

    public function finishOrderAsFailed(OrderSubStatus $subStatus): void
    {
        $order = Order::where('id', $this->orderID)->lockForUpdate()->first();

        if ($order->status->notEquals(OrderStatus::PENDING)) {
            throw OrderException::orderAlreadyFinished($order);
        }

        $order->update([
            'status' => OrderStatus::FAIL,
            'sub_status' => $subStatus,
            'finished_at' => now()
        ]);

        OrderFinishedAsFailedEvent::dispatch($order);
    }

    public function reopenFinishedOrder(OrderSubStatus $subStatus): void
    {
        $order = Order::where('id', $this->orderID)->lockForUpdate()->first();

        if ($order->status->equals(OrderStatus::PENDING)) {
            throw OrderException::orderAlreadyOpened($order);
        }

        $status = $order->status;

        $order->update([
            'status' => OrderStatus::PENDING,
            'sub_status' => $subStatus,
            'finished_at' => null
        ]);

        if ($status->equals(OrderStatus::SUCCESS)) {
            OrderReopenedFromSucessfulEvent::dispatch($order);
        } else if ($status->equals(OrderStatus::FAIL)) {
            OrderReopenedFromFailedEvent::dispatch($order);
        }
    }
}
