<?php

namespace App\Listeners;

use App\Enums\BalanceType;
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
            services()->wallet()->takeFromBalance(
                $event->order->paymentDetail->user->wallet,
                $event->order->trader_paid_for_order,
                TransactionType::PAYMENT_FOR_OPENED_ORDER,
                BalanceType::TRUST
            );

            (new DailyLimit(
                paymentDetail: $event->order->paymentDetail,
                amount: $event->order->amount
            ))->increment();
        });
    }

    public function viaQueue(): string
    {
        return 'order';
    }
}
