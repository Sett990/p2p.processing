<?php

namespace App\Listeners;

use App\Enums\BalanceType;
use App\Enums\TransactionType;
use App\Events\OrderFinishedAsSuccessfulEvent;
use App\Utils\Transaction;
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
        Transaction::run(function () use ($event) {
            services()->wallet()->giveToBalance(
                $event->order->merchant->user->wallet,
                $event->order->merchant_profit,
                TransactionType::INCOME_FROM_A_SUCCESSFUL_ORDER,
                BalanceType::MERCHANT
            );
        });
    }

    public function viaQueue(): string
    {
        return 'order';
    }
}
