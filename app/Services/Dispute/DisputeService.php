<?php

namespace App\Services\Dispute;

use App\Contracts\DisputeServiceContract;
use App\Enums\DisputeStatus;
use App\Enums\OrderStatus;
use App\Enums\OrderSubStatus;
use App\Exceptions\DisputeException;
use App\Models\Dispute;
use App\Models\Order;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DisputeService implements DisputeServiceContract
{
    /**
     * @throws DisputeException
     */
    public function create(Order $order, UploadedFile $receipt): Dispute
    {
        return $this->lock(function () use ($order, $receipt) {
            if ($order->dispute) {
                throw new DisputeException('Dispute already exists.');
            }

            if ($order->status->equals(OrderStatus::SUCCESS)) {
                throw new DisputeException('You can only open a dispute for a failed or pending order');
            }

            if ($order->status->equals(OrderStatus::PENDING)) {
                services()->order()->finishOrderAsFailed($order, OrderSubStatus::CANCELED);
            }

            $receipt_name = 'receipt_'.strtolower(Str::random(32)).'.'.$receipt->extension();
            $receipt->move(storage_path('receipts'), $receipt_name);

            $dispute = Dispute::create([
                'receipt' => $receipt_name,
                'order_id' => $order->id,
                'trader_id' => $order->paymentDetail->user_id,
                'status' => DisputeStatus::PENDING,
            ]);

            services()->order()->reopenFinishedOrder($order, OrderSubStatus::WAITING_FOR_DISPUTE_TO_BE_RESOLVED);

            return $dispute;
        }, $order);
    }

    public function accept(Dispute $dispute): bool
    {
        return $this->lock(function () use ($dispute) {
            if ($dispute->status->notEquals(DisputeStatus::PENDING)) {
                throw new DisputeException('Dispute must be pending.');
            }

            services()->order()->finishOrderAsSuccessful($dispute->order, OrderSubStatus::SUCCESSFULLY_PAID_BY_RESOLVED_DISPUTE);

            return $dispute->update([
                'status' => DisputeStatus::ACCEPTED
            ]);
        }, $dispute->order);
    }

    public function cancel(Dispute $dispute, string $reason): bool
    {
        return $this->lock(function () use ($dispute, $reason) {
            if ($dispute->status->notEquals(DisputeStatus::PENDING)) {
                throw new DisputeException('Dispute must be pending.');
            }

            services()->order()->finishOrderAsFailed($dispute->order, OrderSubStatus::CANCELED_BY_DISPUTE);

            return $dispute->update([
                'status' => DisputeStatus::CANCELED,
                'reason' => $reason
            ]);
        }, $dispute->order);
    }

    public function rollback(Dispute $dispute): bool
    {
        return $this->lock(function () use ($dispute) {
            if ($dispute->status->equals(DisputeStatus::PENDING)) {
                throw new DisputeException('Cannot rollback pending dispute.');
            }

            services()->order()->reopenFinishedOrder($dispute->order, OrderSubStatus::WAITING_FOR_DISPUTE_TO_BE_RESOLVED);

            return $dispute->update([
                'status' => DisputeStatus::PENDING,
                'reason' => null
            ]);
        }, $dispute->order);
    }

    protected function lock(callable $callback, Order $order): mixed
    {
        return cache()->lock('dispute-lock-'.$order->id, 8)
            ->block(10, function () use ($callback) {
                return DB::transaction(function () use ($callback) {
                    return $callback();
                });
            });
    }
}
