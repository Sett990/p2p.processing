<?php

namespace App\Jobs;

use App\Models\Payout;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

class SendPayoutCallbackJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private Payout $payout,
    )
    {
        $this->onQueue('callback');
        $this->afterCommit();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        services()->callback()->sendForPayout($this->payout);
    }
}
