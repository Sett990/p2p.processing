<?php

namespace App\Console\Commands;

use App\Enums\DisputeStatus;
use App\Jobs\SendTelegramNotificationJob;
use App\Models\Dispute;
use App\Services\TelegramBot\Notifications\NewDispute;
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
                if ($dispute->created_at->lessThan(now()->subHours(18))) {
                    $this->sendTelegramNotificationJob($dispute, 'ИИ офицер обнаружил спор, который уже находится в обработке 18 часов. У вас остаётся 6 часов на решение проблемы, или сделки могут быть остановлены. Решение ИИ офицера можно оспорить в рабочем чате.');
                } elseif ($dispute->created_at->lessThan(now()->subHours(6))) {
                    $this->sendTelegramNotificationJob($dispute, 'Данный спор находится в обработке уже 6 часов. Срочно отпишите в рабочем чате о решении данной проблемы как можно скорее или решите спор самостоятельно.');
                } elseif ($dispute->created_at->lessThan(now()->subHours(2))) {
                    $this->sendTelegramNotificationJob($dispute, 'Спорный тикет не обработан 2 часа. Немедленно примите меры и свяжитесь с ответственными для скорейшего решения проблемы.');
                } elseif ($dispute->created_at->lessThan(now()->subHour())) {
                    $this->sendTelegramNotificationJob($dispute, 'Спор остаётся без обработки уже 1 час. Требуется незамедлительное принятие мер по его разрешению.');
                } elseif ($dispute->created_at->lessThan(now()->subMinutes(30))) {
                    $this->sendTelegramNotificationJob($dispute, 'Спорный тикет не был обработан в течение 30 минут. Срочно вмешайтесь для устранения проблемы.');
                } elseif ($dispute->created_at->lessThan(now()->subMinutes(10))) {
                    $this->sendTelegramNotificationJob($dispute, 'Спорный ордер остаётся необработанным уже 10 минут. Проверьте статус и примите оперативные действия.');
                } elseif ($dispute->created_at->lessThan(now()->subMinutes(5))) {
                    $this->sendTelegramNotificationJob($dispute, 'Спорный чек не обработан в течение 5 минут. Пожалуйста, примите необходимые меры.');
                }
            });
    }

    protected function sendTelegramNotificationJob(Dispute $dispute, string $message): void
    {
        if ($dispute->order->paymentDetail->user->telegram) {
            SendTelegramNotificationJob::dispatch(
                new PendingDispute(
                    telegram: $dispute->order->paymentDetail->user->telegram,
                    dispute: $dispute,
                    message: $message
                )
            );
        }
    }
}
