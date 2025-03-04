<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DisconnectInactiveUsersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:disconnect-inactive-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Отключает пользователей, которые не получали сделки более 6 часов';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Получаем всех онлайн пользователей
        $onlineUsers = User::query()
            ->where('is_online', true)
            ->where('updated_at', '<', Carbon::now()->subHours(3))
            //->orWhere('is_payout_online', true)
            ->get();

        foreach ($onlineUsers as $user) {
            // Получаем последнюю сделку пользователя
            $lastOrder = Order::where('trader_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->first();

            if (! $lastOrder) {
                continue;
            }

            // Если у пользователя последняя сделка старше 6 часов
            if ($lastOrder->created_at->diffInHours(Carbon::now()) >= 6) {
                // Отключаем пользователя
                $user->is_online = false;
                $user->save();
            }
        }

        return Command::SUCCESS;
    }
}
