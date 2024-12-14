<?php

namespace App\Console\Commands;

use App\Enums\DetailType;
use App\Models\Dispute;
use App\Models\Notification;
use App\Models\Order;
use App\Models\PaymentGateway;
use App\Models\SmsParser;
use App\Models\User;
use App\Services\Money\Currency;
use App\Services\TelegramBot\Notifications\AdminGlobal;
use App\Services\TelegramBot\Notifications\NewDispute;
use App\Services\TelegramBot\Notifications\NewOrder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Telegram\Bot\Laravel\Facades\Telegram;

class InstallAppCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:install';

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
        if (! $this->confirm('Вы уверены что хотите запустить установку приложения?')) {
            return;
        }

        if (! $this->confirm('Вы точно уверены? Установка перезапишет все имеющиеся данные.')) {
            return;
        }

        if (! $this->confirm('Я предупреждал!')) {
            return;
        }

        services()->telegramBot()->setWebhook();

        Artisan::call('migrate:fresh');

        $user = User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@admin.com',
        ]);

        services()->wallet()->create($user);

        //create roles and permissions
        $role_admin = Role::create(['name' => 'Super Admin']);
        Role::create(['name' => 'Trader']);
        Role::create(['name' => 'Merchant']);

        $permission = Permission::create(['name' => 'access admin panel']);
        $role_admin->givePermissionTo($permission);

        //assign roles
        $user->assignRole($role_admin);

        $payment_gateways = [
            ['id' => 1, 'name' => 'Сбербанк', 'code' => 'sberbank_rub', 'currency' => Currency::RUB(), 'detail_types' => [
                DetailType::CARD, DetailType::ACCOUNT_NUMBER,
            ], 'sms_senders' => ['900']],
            ['id' => 2, 'name' => 'Альфа-Банк', 'code' => 'alfabank_rub', 'currency' => Currency::RUB(), 'detail_types' => [
                DetailType::CARD, DetailType::ACCOUNT_NUMBER,
            ]],
            ['id' => 3, 'name' => 'Райффайзенбанк', 'code' => 'raiffeisen_rub', 'currency' => Currency::RUB(), 'detail_types' => [DetailType::CARD]],
            ['id' => 4, 'name' => 'СБП', 'code' => 'sbp_rub', 'currency' => Currency::RUB(), 'detail_types' => [DetailType::PHONE], 'sub_payment_gateways' => [1, 2, 3]],
            //['id' => 5, 'name' => 'HUMO', 'code' => 'humo_uzs', 'currency' => Currency::UZS(), 'detail_types' => [DetailType::CARD]],
            //['id' => 6, 'name' => 'UZCARD', 'code' => 'uzcard_uzs', 'currency' => Currency::UZS(), 'detail_types' => [DetailType::CARD]],
            ['id' => 7, 'name' => 'Halyk', 'code' => 'halyk_kzt', 'currency' => Currency::KZT(), 'detail_types' => [DetailType::CARD]],
            ['id' => 8, 'name' => 'Jusan', 'code' => 'jusan_kzt', 'currency' => Currency::KZT(), 'detail_types' => [DetailType::CARD]],
            ['id' => 9, 'name' => 'Eurasian', 'code' => 'eurasian_kzt', 'currency' => Currency::KZT(), 'detail_types' => [DetailType::CARD]],
            ['id' => 10, 'name' => 'ОТП', 'code' => 'otp_rub', 'currency' => Currency::RUB(), 'detail_types' => [DetailType::CARD]],
            ['id' => 11, 'name' => 'ПСБ', 'code' => 'psb_rub', 'currency' => Currency::RUB(), 'detail_types' => [DetailType::CARD]],
            ['id' => 12, 'name' => 'МТС Банк', 'code' => 'mts_rub', 'currency' => Currency::RUB(), 'detail_types' => [DetailType::CARD]],
            ['id' => 13, 'name' => 'ДОМ.РФ', 'code' => 'domrf_rub', 'currency' => Currency::RUB(), 'detail_types' => [DetailType::CARD]],
            ['id' => 14, 'name' => 'Росбанк', 'code' => 'rosbank_rub', 'currency' => Currency::RUB(), 'detail_types' => [DetailType::CARD]],
        ];

        foreach ($payment_gateways as $payment_gateway) {
            PaymentGateway::create([
                'name' => $payment_gateway['name'],
                'code' => $payment_gateway['code'],
                'currency' => $payment_gateway['currency'],
                'min_limit' => 1000,
                'max_limit' => 100000,
                'sms_senders' => $payment_gateway['sms_senders'] ?? [],
                'commission_rate' => 2.5,
                'detail_types' => $payment_gateway['detail_types'],
                'sub_payment_gateways' => ! empty($payment_gateway['sub_payment_gateways']) ? $payment_gateway['sub_payment_gateways'] : [],
            ]);
        }

        $parsers = [
            [
                'payment_gateway_id' => 1,
                'format' => "MIR-0000 21:27 Перевод из Ozon банк +3455р от СЕРГЕЙ П. Баланс: 50612.66р",
                'regex' => "^(?<card_type>[A-Z]{3,4})-(?<card_last_digits>\d{4})\s\d{2}:\d{2}\sПеревод\sиз\s.+\s\+(?<amount>\d+(.\d+){0,3})р\sот\s.+\sБаланс:\s.+р(\s«#\d+»)?$",
            ],
            [
                'payment_gateway_id' => 1,
                'format' => "СЧЁТ0000 15:19 Перевод 10р от Артём К. Баланс: 5196.25р",
                'regex' => "^СЧЁТ(?<card_last_digits>\d{4})\s\d{2}:\d{2}\sПеревод\s(?<amount>\d+(.\d+){0,3})р\sот\s.+\sБаланс:\s.+р$",
            ],
            [
                'payment_gateway_id' => 1,
                'format' => "ECMC0000 00:18 Перевод 3758.01р от Иван Д. Баланс: 19475.10р",
                'regex' => "^(?<card_type>[A-Z]{3,4})(?<card_last_digits>\d{4})\s\d{2}:\d{2}\sПеревод\s(?<amount>\d+(.\d+){0,3})р\sот\s.+\sБаланс:\s.+р$",
            ],
            [
                'payment_gateway_id' => 1,
                'format' => "MIR-0000 15:19 Перевод 10р от Артём К. Баланс: 5186.25р",
                'regex' => "^(?<card_type>[A-Z]{3,4})-(?<card_last_digits>\d{4})\s\d{2}:\d{2}\sПеревод\s(?<amount>\d+(.\d+){0,3})р\sот\s.+\sБаланс:\s.+р$",
            ],
            [
                'payment_gateway_id' => 1,
                'format' => "СЧЁТ0000 31.07.24 зачислен перевод по СБП 5000р из Т-Банк от АННА ВАДИМОВНА Д.",
                'regex' => "^СЧЁТ(?<card_last_digits>\d{4})\s\d{2}\.\d{2}\.\d{2}\sзачислен\sперевод\sпо\sСБП\s(?<amount>\d+(.\d+){0,3})р\sиз\s.+\sот\s.+$",
            ],
            [
                'payment_gateway_id' => 1,
                'format' => "Перевод из Тинькофф Банк +768.11р от Иван К. СЧЁТ0000 — Баланс: 10800.50р",
                'regex' => "^Перевод\sиз\s.+\s\+(?<amount>\d+(.\d+){0,3})р\sот\s.+\СЧЁТ(?<card_last_digits>\d{4})\s—\sБаланс:\s.+р$",
            ],
        ];

        foreach ($parsers as $parser) {
            SmsParser::create([
                'payment_gateway_id' => $parser['payment_gateway_id'],
                'format' => $parser['format'],
                'regex' => $parser['regex'],
            ]);
        }

        services()->settings()->createAll();

        //commands
        Artisan::call('app:update-p2p-prices');
        Artisan::call('app:load-payment-methods-list');
    }
}
