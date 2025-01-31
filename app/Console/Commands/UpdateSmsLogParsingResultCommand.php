<?php

namespace App\Console\Commands;

use App\Models\SmsLog;
use App\Services\Sms\Parser;
use Illuminate\Console\Command;

class UpdateSmsLogParsingResultCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-sms-log-parsing-result';

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
        SmsLog::query()
            ->chunk(500, function ($logs) {
                $logs->each(function (SmsLog $log) {
                    $parser = new Parser();

                    $parsingResult = [
                        'amount' => $parser->parseAmountFromMessage($log->message),
                        'card' => $parser->parseCardLastDigitsFromMessage($log->message),
                    ];

                    $log->update([
                        'parsing_result' => $parsingResult['amount'] ? $parsingResult : null,
                    ]);
                });
            });
    }
}
