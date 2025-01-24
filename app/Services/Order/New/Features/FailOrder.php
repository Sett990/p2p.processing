<?php

namespace App\Services\Order\Features;

use App\Enums\OrderStatus;
use App\Enums\TransactionType;
use App\Exceptions\OrderException;
use App\Models\Order;

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
            $this->order->update([
                'status' => OrderStatus::FAIL,
                'finished_at' => now()
            ]);

            services()->wallet()->giveTrust(
                wallet: $this->order->paymentDetail->user->wallet,
                amount: $this->order->profit,
                type: $this->transactionType
            );
        } else {
            throw OrderException::make('Cant fail not pending order');
        }

        return true;
    }
}
