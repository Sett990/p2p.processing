<?php

namespace App\Services\Order\Features;

use App\Enums\OrderStatus;
use App\Events\OrderReopenedFromFailedEvent;
use App\Events\OrderReopenedFromSucessfulEvent;
use App\Events\OrderFinishedAsFailedEvent;
use App\Events\OrderFinishedAsSuccessfulEvent;
use App\Exceptions\OrderException;
use App\Models\Order;

class OrderOperator
{
    public function __construct(protected Order $order)
    {}

    public function finishOrderAsSuccessful(): void
    {
        if ($this->order->status->notEquals(OrderStatus::PENDING)) {
            throw OrderException::orderAlreadyFinished($this->order);
        }

        $this->order->update([
            'status' => OrderStatus::SUCCESS,
            'finished_at' => now()
        ]);

        OrderFinishedAsSuccessfulEvent::dispatch($this->order);
    }

    public function finishOrderAsFailed(): void
    {
        if ($this->order->status->notEquals(OrderStatus::PENDING)) {
            throw OrderException::orderAlreadyFinished($this->order);
        }

        $this->order->update([
            'status' => OrderStatus::FAIL,
            'finished_at' => now()
        ]);

        OrderFinishedAsFailedEvent::dispatch($this->order);
    }

    public function reopenFinishedOrder(): void
    {
        if ($this->order->status->equals(OrderStatus::PENDING)) {
            throw OrderException::orderAlreadyOpened($this->order);
        }

        $status = $this->order->status;

        $this->order->update([
            'status' => OrderStatus::PENDING,
            'finished_at' => null
        ]);

        if ($status->equals(OrderStatus::SUCCESS)) {
            OrderReopenedFromSucessfulEvent::dispatch($this->order);
        } else if ($status->equals(OrderStatus::FAIL)) {
            OrderReopenedFromFailedEvent::dispatch($this->order);
        }
    }
}
