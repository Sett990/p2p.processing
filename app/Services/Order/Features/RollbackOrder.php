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
                    $this->order->paymentDetail->user->wallet->takeFromTrust(
                        amount: $this->order->profit,
                        type: $this->transactionType
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
