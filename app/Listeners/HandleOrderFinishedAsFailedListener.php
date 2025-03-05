<?php

namespace App\Listeners;

use App\Enums\BalanceType;
use App\Enums\TransactionType;
use App\Events\OrderFinishedAsFailedEvent;
use App\Services\Order\Utils\DailyLimit;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;

class HandleOrderFinishedAsFailedListener implements ShouldQueue
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
    public function handle(OrderFinishedAsFailedEvent $event): void
    {
        DB::transaction(function () use ($event) {
            services()->wallet()->giveToBalance(
                $event->order->paymentDetail->user->wallet,
                $event->order->trader_paid_for_order,
                TransactionType::REFUND_FOR_CANCELED_ORDER,
                BalanceType::TRUST
            );

            (new DailyLimit(
                paymentDetail: $event->order->paymentDetail,
                amount: $event->order->amount
            ))->decrement();
        });
    }

    public function viaQueue(): string
    {
        return 'order';
    }
}
