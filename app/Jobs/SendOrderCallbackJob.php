<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

class SendOrderCallbackJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public int $tries = 8;
    public int $timeout = 5;

    public function __construct(
        private Order $order,
    )
    {
        $this->onQueue('callback');
        $this->afterCommit();
    }

    public function handle(): void
    {
        services()->callback()->sendForOrder($this->order);
    }

    public function backoff(): array //8 попыток
    {
        return [10, 60, 120, 240, 480, 1800, 3600, 7200]; // Интервалы в секундах перед повторными попытками
    }
}
