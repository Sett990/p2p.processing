<?php

namespace App\Observers;

use App\Jobs\SendPayoutCallbackJob;
use App\Models\Payout;

class PayoutObserver
{
    public $afterCommit = true;

    /**
     * Handle the Payout "created" event.
     */
    public function created(Payout $payout): void
    {
        SendPayoutCallbackJob::dispatch($payout);
    }

    /**
     * Handle the Payout "updated" event.
     */
    public function updated(Payout $payout): void
    {
        if ($payout->wasChanged('status')) {
            SendPayoutCallbackJob::dispatch($payout);
        }
    }

    /**
     * Handle the Payout "deleted" event.
     */
    public function deleted(Payout $payout): void
    {
        //
    }

    /**
     * Handle the Payout "restored" event.
     */
    public function restored(Payout $payout): void
    {
        //
    }

    /**
     * Handle the Payout "force deleted" event.
     */
    public function forceDeleted(Payout $payout): void
    {
        //
    }
}
