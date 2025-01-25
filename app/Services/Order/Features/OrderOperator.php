<?php

namespace App\Services\Order\Features\OrderOperator;

use App\Enums\OrderStatus;
use App\Enums\TransactionType;
use App\Exceptions\OrderException;
use App\Models\Order;
use App\Services\Order\Utils\DailyLimit;
use Illuminate\Support\Facades\DB;

class OrderOperator
{
    public function __construct(protected Order $order)
    {}

    public function finishOrderAsSuccessful(): true
    {
        if ($this->order->status->equals(OrderStatus::PENDING)) {
            DB::transaction(function () {
                $this->order->update([
                    'status' => OrderStatus::SUCCESS,
                    'finished_at' => now()
                ]);

                $this->order->merchant->user->wallet->giveToMerchant(
                    amount: $this->order->merchant_profit,
                    type: TransactionType::INCOME_FROM_A_SUCCESSFUL_ORDER
                );
            });
        } else {
            throw OrderException::make('Cant succeed not pending order');
        }

        return true;
    }

    public function finishOrderAsFailed(TransactionType $transactionType): true
    {
        if ($this->order->status->equals(OrderStatus::PENDING)) {
            DB::transaction(function () use ($transactionType) {
                $this->order->update([
                    'status' => OrderStatus::FAIL,
                    'finished_at' => now()
                ]);

                $this->order->paymentDetail->user->wallet->giveToTrust(
                    amount: $this->order->profit,
                    type: $transactionType
                );

                (new DailyLimit(
                    paymentDetail: $this->order->paymentDetail,
                    amount: $this->order->amount
                ))->decrement();
            });
        } else {
            throw OrderException::make('Cant fail not pending order');
        }

        return true;
    }

    public function reopenFinishedOrder(TransactionType $transactionType): bool
    {
        if ($this->order->status->notEquals(OrderStatus::PENDING)) {
            DB::transaction(function () use ($transactionType) {
                if ($this->order->status->equals(OrderStatus::FAIL)) {
                    $this->order->paymentDetail->user->wallet->takeFromTrust(
                        amount: $this->order->profit,
                        type: $transactionType
                    );
                    (new DailyLimit(
                        paymentDetail: $this->order->paymentDetail,
                        amount: $this->order->amount
                    ))->increment();
                }
                if ($this->order->status->equals(OrderStatus::SUCCESS)) {
                    $this->order->merchant->user->wallet->takeFromMerchant(
                        amount: $this->order->merchant_profit,
                        type: TransactionType::ROLLBACK_INCOME_FROM_A_SUCCESSFUL_ORDER
                    );
                }

                $this->order->update([
                    'status' => OrderStatus::PENDING,
                    'finished_at' => null
                ]);
            });
        } else {
            throw OrderException::make('Cant rollback order');
        }

        return true;
    }
}
