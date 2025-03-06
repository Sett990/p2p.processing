<?php

namespace App\Listeners;

use App\Enums\BalanceType;
use App\Enums\TransactionType;
use App\Events\OrderFinishedAsSuccessfulEvent;
use Illuminate\Contracts\Queue\ShouldQueue;

class HandleOrderFinishedAsSuccessfulListener implements ShouldQueue
{
    public int $tries = 3;

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
        services()->wallet()->giveToBalance(
            $event->order->merchant->user->wallet->id,
            $event->order->merchant_profit,
            TransactionType::INCOME_FROM_A_SUCCESSFUL_ORDER,
            BalanceType::MERCHANT
        );
    }

    public function viaQueue(): string
    {
        return 'order';
    }

    public function backoff(): array
    {
        return [5, 10, 15];
    }
}
