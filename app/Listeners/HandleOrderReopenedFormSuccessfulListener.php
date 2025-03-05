<?php

namespace App\Listeners;

use App\Enums\BalanceType;
use App\Enums\TransactionType;
use App\Events\OrderReopenedFromSucessfulEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;

class HandleOrderReopenedFormSuccessfulListener implements ShouldQueue
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
    public function handle(OrderReopenedFromSucessfulEvent $event): void
    {
        DB::transaction(function () use ($event) {
            services()->wallet()->takeFromBalance(
                $event->order->merchant->user->wallet,
                $event->order->merchant_profit,
                TransactionType::ROLLBACK_INCOME_FROM_A_SUCCESSFUL_ORDER,
                BalanceType::MERCHANT
            );
        });
    }

    public function viaQueue(): string
    {
        return 'order';
    }
}
