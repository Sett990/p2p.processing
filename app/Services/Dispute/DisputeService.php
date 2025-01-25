<?php

namespace App\Services\Dispute;

use App\Contracts\DisputeServiceContract;
use App\Enums\DisputeStatus;
use App\Enums\OrderStatus;
use App\Enums\TransactionType;
use App\Exceptions\DisputeException;
use App\Models\Dispute;
use App\Models\Order;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class DisputeService implements DisputeServiceContract
{
    /**
     * @throws DisputeException
     */
    public function create(Order $order, UploadedFile $receipt): Dispute
    {
        if ($order->dispute) {
            throw new DisputeException('Dispute already exists.');
        }

        if ($order->status->notEquals(OrderStatus::FAIL)) {
            throw new DisputeException('You can only open a dispute for a failed order');
        }

        $receipt_name = 'receipt_'.strtolower(Str::random(32)).'.'.$receipt->extension();
        $receipt->move(storage_path('receipts'), $receipt_name);

        services()->order()->reopenFinishedOrder($order, TransactionType::PAYMENT_FOR_OPENED_DISPUTE);

        return Dispute::create([
            'receipt' => $receipt_name,
            'order_id' => $order->id,
            'status' => DisputeStatus::PENDING,
        ]);
    }

    public function accept(Dispute $dispute): bool
    {
        if ($dispute->status->notEquals(DisputeStatus::PENDING)) {
            throw new DisputeException('Dispute must be pending.');
        }

        services()->order()->finishOrderAsSuccessful($dispute->order);

        return $dispute->update([
            'status' => DisputeStatus::ACCEPTED
        ]);
    }

    public function cancel(Dispute $dispute, string $reason): bool
    {
        if ($dispute->status->notEquals(DisputeStatus::PENDING)) {
            throw new DisputeException('Dispute must be pending.');
        }

        services()->order()->finishOrderAsFailed($dispute->order, TransactionType::REFUND_FOR_CANCELED_DISPUTE);

        return $dispute->update([
            'status' => DisputeStatus::CANCELED,
            'reason' => $reason
        ]);
    }

    public function rollback(Dispute $dispute): bool
    {
        if ($dispute->status->equals(DisputeStatus::PENDING)) {
            throw new DisputeException('Cannot rollback pending dispute.');
        }

        services()->order()->reopenFinishedOrder($dispute->order, TransactionType::PAYMENT_FOR_OPENED_DISPUTE);

        return $dispute->update([
            'status' => DisputeStatus::PENDING,
            'reason' => null
        ]);
    }
}
