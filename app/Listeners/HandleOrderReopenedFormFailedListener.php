<?php

namespace App\Listeners;

use App\Enums\TransactionType;
use App\Events\OrderReopenedFromFailedEvent;
use App\Services\Order\Utils\DailyLimit;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;

class HandleOrderReopenedFormFailedListener implements ShouldQueue
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
    public function handle(OrderReopenedFromFailedEvent $event): void
    {
        DB::transaction(function () use ($event) {
            $event->order->paymentDetail->user->wallet->takeFromTrust(
                amount: $event->order->profit,
                type: TransactionType::PAYMENT_FOR_OPENED_ORDER
            );

            (new DailyLimit(
                paymentDetail: $event->order->paymentDetail,
                amount: $event->order->amount
            ))->increment();
        });
    }

    public function viaQueue(): string
    {
        return 'default';
    }
}
