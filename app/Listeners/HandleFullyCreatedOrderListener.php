<?php

namespace App\Listeners;

use App\Events\OrderFullyCreatedEvent;
use App\Jobs\ExpiresOrderJob;
use App\Jobs\SendOrderCallbackJob;
use App\Jobs\SendTelegramNotificationJob;
use App\Services\TelegramBot\Notifications\NewOrder;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class HandleFullyCreatedOrderListener implements ShouldQueue
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
    public function handle(OrderFullyCreatedEvent $event): void
    {
        ExpiresOrderJob::dispatch($event->order)->delay($event->order->expires_at);

        SendOrderCallbackJob::dispatch($event->order);
        
        if ($event->order->paymentDetail->user->telegram) {
            SendTelegramNotificationJob::dispatch(
                new NewOrder(
                    telegram: $event->order->paymentDetail->user->telegram,
                    order: $event->order
                )
            );
        }
    }

    public function viaQueue(): string
    {
        return 'default';
    }
}
