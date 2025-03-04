<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();


Schedule::command('app:update-p2p-prices')->everyMinute();
Schedule::command('app:close-manually-orders')->everyMinute();
Schedule::command('app:execute-funds-on-hold')->everyMinute();
Schedule::command('app:reset-payment-detail-limits')->dailyAt('00:00');
Schedule::command('app:load-payment-methods-list')->hourly();
Schedule::command('telescope:prune --hours=48')->daily();
Schedule::command('app:clear-trash-from-sms-log-command')->daily();
Schedule::command('app:notify-trader-about-pending-disputes')->everyMinute();
Schedule::command('app:disconnect-inactive-users')->everyThirtyMinutes();
