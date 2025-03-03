<?php

namespace App\Listeners;

use App\Models\User;
use Illuminate\Auth\Events\Failed;

class LogFailedLogin
{
    /**
     * Create the event listener.
     */
    public function __construct() {}

    /**
     * Handle the event.
     */
    public function handle(Failed $event): void
    {
        // Записываем только если пользователь существует
        if ($event->user instanceof User) {
            services()->loginHistory()->recordLogin(
                $event->user,
                request(),
                false
            );
        }
    }
}
