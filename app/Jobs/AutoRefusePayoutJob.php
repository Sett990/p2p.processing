<?php

namespace App\Jobs;

use App\Enums\PayoutStatus;
use App\Models\Payout;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AutoRefusePayoutJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private Payout $payout,
        private User $trader,
    )
    {
        $this->afterCommit();
        $this->onQueue('payout');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->payout->status->equals(PayoutStatus::PENDING) && $this->payout->trader_id && $this->payout->trader_id === $this->trader->id) {
            services()->payout()->refusePayout($this->payout, 'Трейдер не успел исполнить выплату.');
        }
    }
}
