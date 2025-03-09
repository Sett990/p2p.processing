<?php

namespace App\Listeners;

use App\Enums\BalanceType;
use App\Enums\TransactionType;
use App\Events\DetailsAssignedToOrderEvent;
use App\Jobs\ExpiresOrderJob;
use App\Jobs\SendOrderCallbackJob;
use App\Jobs\SendTelegramNotificationJob;
use App\Services\Order\Utils\DailyLimit;
use App\Services\TelegramBot\Notifications\NewOrder;
use App\Utils\Transaction;
use Illuminate\Contracts\Queue\ShouldQueue;

class HandleDetailsAssignedToOrderListener implements ShouldQueue
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
    public function handle(DetailsAssignedToOrderEvent $event): void
    {
        Transaction::run(function () use ($event) {
            DailyLimit::increment($event->order->payment_detail_id, $event->order->amount, $event->order->created_at);

            services()->wallet()->takeFromBalance(
                $event->order->trader->wallet->id,
                $event->order->trader_paid_for_order,
                TransactionType::PAYMENT_FOR_OPENED_ORDER,
                BalanceType::TRUST
            );

            ExpiresOrderJob::dispatch($event->order)->delay($event->order->expires_at);

            SendOrderCallbackJob::dispatch($event->order);

            if ($event->order->trader->telegram) {
                SendTelegramNotificationJob::dispatch(
                    new NewOrder(
                        telegram: $event->order->paymentDetail->user->telegram,
                        order: $event->order
                    )
                );
            }
        });
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
