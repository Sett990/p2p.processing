<?php

namespace App\Listeners;

use App\Enums\TransactionType;
use App\Events\OrderFinishedAsSuccessfulEvent;
use Illuminate\Contracts\Queue\ShouldQueue;

class HandleOrderFinishedAsSuccessfulListener implements ShouldQueue
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
    public function handle(OrderFinishedAsSuccessfulEvent $event): void
    {
        $event->order->merchant->user->wallet->giveToMerchant(
            amount: $event->order->merchant_profit,
            type: TransactionType::INCOME_FROM_A_SUCCESSFUL_ORDER
        );
    }

    public function viaQueue(): string
    {
        return 'order';
    }
}
