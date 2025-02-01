<?php

namespace App\Listeners;

use App\Enums\OrderStatus;
use App\Enums\TransactionType;
use App\Events\OrderReopenedEvent;
use App\Services\Order\Utils\DailyLimit;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;

class HandleOrderReopenedListener implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(OrderReopenedEvent $event): void
    {
        DB::transaction(function () use ($event) {
            if ($event->order->status->equals(OrderStatus::FAIL)) {
                $event->order->paymentDetail->user->wallet->takeFromTrust(
                    amount: $event->order->profit,
                    type: TransactionType::PAYMENT_FOR_OPENED_ORDER
                );
                (new DailyLimit(
                    paymentDetail: $event->order->paymentDetail,
                    amount: $event->order->amount
                ))->increment();
            }
            if ($event->order->status->equals(OrderStatus::SUCCESS)) {
                $event->order->merchant->user->wallet->takeFromMerchant(
                    amount: $event->order->merchant_profit,
                    type: TransactionType::ROLLBACK_INCOME_FROM_A_SUCCESSFUL_ORDER
                );
            }
        });
    }

    public function viaQueue(): string
    {
        return 'default';
    }
}
