<?php

namespace App\Listeners;

use App\Models\User;
use App\Services\Auth\LoginHistoryService;
use Illuminate\Auth\Events\Failed;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogFailedLogin
{
    /**
     * Create the event listener.
     */
    public function __construct(
        protected LoginHistoryService $loginHistoryService
    ) {}

    /**
     * Handle the event.
     */
    public function handle(Failed $event): void
    {
        // Записываем только если пользователь существует
        if ($event->user instanceof User) {
            $this->loginHistoryService->recordLogin(
                $event->user,
                request(),
                false
            );
        }
    }
}
