<?php

namespace App\Services\Order\Features;

use App\Enums\OrderStatus;
use App\Enums\TransactionType;
use App\Exceptions\OrderException;
use App\Models\Order;
use App\Services\Order\Utils\DailyLimit;
use Illuminate\Support\Facades\DB;

class RollbackOrder extends BaseFeature
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
        if ($this->order->status->notEquals(OrderStatus::PENDING)) {
            DB::transaction(function () {
                if ($this->order->status->equals(OrderStatus::FAIL)) {
                    services()->wallet()->takeFromTrust(
                        wallet: $this->order->paymentDetail->user->wallet,
                        amount: $this->order->profit,
                        type: $this->transactionType
                    );
                    (new DailyLimit(
                        paymentDetail: $this->order->paymentDetail,
                        amount: $this->order->amount
                    ))->increment();
                }
                if ($this->order->status->equals(OrderStatus::SUCCESS)) {
                    services()->wallet()->takeFromMerchant(
                        wallet: $this->order->merchant->user->wallet,
                        amount: $this->order->merchant_profit,
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
