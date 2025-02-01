<?php

namespace App\Listeners;

use App\Enums\TransactionType;
use App\Events\OrderReopenedFromSucessfulEvent;
use Illuminate\Contracts\Queue\ShouldQueue;

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
        $event->order->merchant->user->wallet->takeFromMerchant(
            amount: $event->order->merchant_profit,
            type: TransactionType::ROLLBACK_INCOME_FROM_A_SUCCESSFUL_ORDER
        );
    }

    public function viaQueue(): string
    {
        return 'default';
    }
}
