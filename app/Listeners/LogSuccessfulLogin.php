<?php

namespace App\Listeners;

use App\Services\Auth\LoginHistoryService;
use Illuminate\Auth\Events\Login;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogSuccessfulLogin
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
    public function handle(Login $event): void
    {
        $this->loginHistoryService->recordLogin(
            $event->user,
            request(),
            true
        );
    }
}
