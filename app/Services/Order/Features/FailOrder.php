<?php

namespace App\Services\Order\Features;

use App\Enums\OrderStatus;
use App\Enums\TransactionType;
use App\Exceptions\OrderException;
use App\Models\Order;
use App\Services\Order\Utils\DailyLimit;
use Illuminate\Support\Facades\DB;

class FailOrder extends BaseFeature
{
    public function __construct(
        protected Order $order,
        protected TransactionType $transactionType,
    )
    {}

    /**
     * @throws OrderException
     */
    public function handle(): bool
    {
        if ($this->order->status->equals(OrderStatus::PENDING)) {
            DB::transaction(function () {
                $this->order->update([
                    'status' => OrderStatus::FAIL,
                    'finished_at' => now()
                ]);

                $this->order->paymentDetail->user->wallet->giveToTrust(
                    amount: $this->order->profit,
                    type: $this->transactionType
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
}
