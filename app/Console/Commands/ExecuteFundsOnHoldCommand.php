<?php

namespace App\Console\Commands;

use App\Enums\FundsOnHoldStatus;
use App\Models\FundsOnHold;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ExecuteFundsOnHoldCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:execute-funds-on-hold';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        FundsOnHold::query()
            ->where('status', FundsOnHoldStatus::PENDING_FOR_EXECUTION)
            ->whereDate('hold_until', '<', now())
            ->get()
            ->each(function (FundsOnHold $fundsOnHold) {
                DB::transaction(function () use ($fundsOnHold) {
                    services()->fundsHolder()->execute($fundsOnHold);
                });
            });
    }
}
