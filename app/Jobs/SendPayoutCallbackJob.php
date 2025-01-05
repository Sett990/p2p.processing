<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendOrderCallbackJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private Order $order,
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
        services()->orderCallback()->send($this->order);
    }
}
