<?php

namespace App\Observers;

use App\Jobs\ExpiresOrderJob;
use App\Jobs\SendOrderCallbackJob;
use App\Jobs\SendTelegramNotificationJob;
use App\Models\Order;
use App\Services\TelegramBot\Notifications\NewOrder;

class OrderObserver
{
    /**
     * Handle the Order "created" event.
     */
    public function created(Order $order): void
    {
        if (! $order->is_manually) {
            ExpiresOrderJob::dispatch($order)->delay($order->expires_at);

            if ($order->paymentDetail->user->telegram) {
                SendTelegramNotificationJob::dispatch(
                    new NewOrder(
                        telegram: $order->paymentDetail->user->telegram,
                        order: $order
                    )
                );
            }
        }
    }

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        if ($order->is_manually && $order->isDirty('payment_detail_id')) {
            ExpiresOrderJob::dispatch($order)->delay($order->expires_at);

            if ($order->paymentDetail->user->telegram) {
                SendTelegramNotificationJob::dispatch(
                    new NewOrder(
                        telegram: $order->paymentDetail->user->telegram,
                        order: $order
                    )
                );
            }

            SendOrderCallbackJob::dispatch($order);
        } else {
            if($order->isDirty('status')){
                SendOrderCallbackJob::dispatch($order);
            }
        }
    }

    /**
     * Handle the Order "deleted" event.
     */
    public function deleted(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "restored" event.
     */
    public function restored(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "force deleted" event.
     */
    public function forceDeleted(Order $order): void
    {
        //
    }
}
