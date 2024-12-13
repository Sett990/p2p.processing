<?php

namespace App\Services\Order\Features;

use App\Enums\OrderStatus;
use App\Exceptions\OrderException;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class SucceedOrder extends BaseFeature
{
    public function __construct(
        protected Order $order,
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
                    'status' => OrderStatus::SUCCESS,
                    'finished_at' => now()
                ]);

                services()->wallet()->giveToMerchant(
                    wallet: $this->order->merchant->user->wallet,
                    amount: $this->order->merchant_profit,
                );
            });
        } else {
            throw OrderException::make('Cant succeed not pending order');
        }

        return true;
    }
}
