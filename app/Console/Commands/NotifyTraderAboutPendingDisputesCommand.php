<?php

namespace App\Console\Commands;

use App\Enums\DisputeStatus;
use App\Jobs\SendTelegramNotificationJob;
use App\Models\Dispute;
use App\Services\TelegramBot\Notifications\PendingDispute;
use Illuminate\Console\Command;

class NotifyTraderAboutPendingDisputesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:notify-trader-about-pending-disputes';

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
        Dispute::query()
            ->where('status', DisputeStatus::PENDING)
            ->where('created_at', '<', now()->subMinutes(5))
            ->whereHas('order.paymentDetail.user.telegram')
            ->with('order.paymentDetail.user.telegram')
            ->each(function (Dispute $dispute) {
                if (! optional($dispute->order->paymentDetail->user)->telegram) {
                    return;
                }

                $notifications = [
                    '18h' => now()->subHours(18),
                    '6h'  => now()->subHours(6),
                    '2h'  => now()->subHours(2),
                    '1h'  => now()->subHour(),
                    '30m' => now()->subMinutes(30),
                    '10m' => now()->subMinutes(10),
                    '5m'  => now()->subMinutes(5),
                ];

                $messages = [
                    '18h' => 'Внимание! ИИ офицер обнаружил спор, который уже находится в обработке 18 часов. У вас остаётся 6 часов на решение проблемы, или сделки могут быть остановлены. Решение ИИ офицера можно оспорить в рабочем чате.',
                    '6h'  => 'Данный спор находится в обработке уже 6 часов. Срочно отпишите в рабочем чате о решении данной проблемы как можно скорее или решите спор самостоятельно.',
                    '2h'  => 'Спорный тикет не обработан 2 часа. Немедленно примите меры и свяжитесь с ответственными для скорейшего решения проблемы.',
                    '1h'  => 'Спор остаётся без обработки уже 1 час. Требуется незамедлительное принятие мер по его разрешению.',
                    '30m' => 'Спорный тикет не был обработан в течение 30 минут. Срочно вмешайтесь для устранения проблемы.',
                    '10m' => 'Спорный ордер остаётся необработанным уже 10 минут. Проверьте статус и примите оперативные действия.',
                    '5m'  => 'Спорный чек не обработан в течение 5 минут. Пожалуйста, примите необходимые меры.',
                ];

                foreach ($notifications as $interval => $time) {
                    $cacheKey = "dispute_{$dispute->id}_notified_{$interval}";

                    if (cache()->has($cacheKey)) {
                        return;
                    }

                    // Проверяем, было ли уже отправлено уведомление
                    if ($dispute->created_at->lessThan($time) && !cache()->has($cacheKey)) {

                        // Отправляем уведомление
                       $this->sendTelegramNotificationJob($dispute, $messages[$interval]);

                        // Устанавливаем кеш, чтобы уведомление не отправлялось повторно
                        cache()->put($cacheKey, true, now()->addHours(24)); // TTL больше самого интервала

                        break; // Прерываем цикл, чтобы не отправлять сразу несколько уведомлений
                    }
                }
            });
    }

    protected function sendTelegramNotificationJob(Dispute $dispute, string $message): void
    {
        SendTelegramNotificationJob::dispatch(
            new PendingDispute(
                telegram: $dispute->order->paymentDetail->user->telegram,
                dispute: $dispute,
                message: $message
            )
        );
    }
}
