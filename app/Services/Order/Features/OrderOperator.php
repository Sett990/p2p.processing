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
    public function __construct(protected Order $order)
    {}

    public function finishOrderAsSuccessful(): void
    {
        if ($this->order->status->notEquals(OrderStatus::PENDING)) {
            throw OrderException::orderAlreadyFinished($this->order);
        }

        $this->order->update([
            'status' => OrderStatus::SUCCESS,
            'sub_status' => $this->order->dispute ? OrderSubStatus::SUCCESSFULLY_PAID_BY_RESOLVED_DISPUTE : OrderSubStatus::SUCCESSFULLY_PAID,
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
            'sub_status' => $this->order->dispute ? OrderSubStatus::CANCELED_BY_DISPUTE : OrderSubStatus::EXPIRED,
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
            'sub_status' => $this->order->dispute ? OrderSubStatus::WAITING_FOR_DISPUTE_TO_BE_RESOLVED : OrderSubStatus::WAITING_FOR_PAYMENT,
            'finished_at' => null
        ]);

        if ($status->equals(OrderStatus::SUCCESS)) {
            OrderReopenedFromSucessfulEvent::dispatch($this->order);
        } else if ($status->equals(OrderStatus::FAIL)) {
            OrderReopenedFromFailedEvent::dispatch($this->order);
        }
    }
}
