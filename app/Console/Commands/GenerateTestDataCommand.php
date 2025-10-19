<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\DTO\User\UserCreateDTO;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use App\Models\User;
use App\Models\UserDevice;
use App\Enums\DetailType;
use App\DTO\PaymentDetail\PaymentDetailCreateDTO;
use App\Models\PaymentGateway;
use App\Services\Money\Currency;
use App\Services\Money\Money;
use App\Enums\BalanceType;

class GenerateTestDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-test-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Генерирует тестовые данные для проекта';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Начинаем генерацию тестовых данных...');

        $words = [
            'alex', 'mike', 'john', 'david', 'steve', 'paul', 'mark', 'tom', 'nick', 'joe',
            'anna', 'lisa', 'mary', 'sara', 'kate', 'jane', 'emma', 'lucy', 'rose', 'amy',
            'max', 'dan', 'sam', 'leo', 'ben', 'jake', 'ryan', 'luke', 'noah', 'jack',
            'sophia', 'olivia', 'ava', 'isabella', 'mia', 'charlotte', 'amelia', 'harper', 'evelyn', 'abigail',
            'gamer', 'player', 'pro', 'master', 'boss', 'king', 'queen', 'ninja', 'warrior', 'hunter',
            'cool', 'swift', 'sharp', 'bright', 'dark', 'light', 'fast', 'slow', 'big', 'small'
        ];

        $rolesMap = [
            'merchant' => 'Merchant',
            'trader' => 'Trader',
            'support' => 'Support',
        ];

        $roleIds = [];
        foreach ($rolesMap as $key => $roleName) {
            $role = Role::where('name', $roleName)->first();
            if (! $role) {
                $this->warn("Роль '{$roleName}' не найдена. Пропускаю создание пользователей этой роли.");
                continue;
            }
            $roleIds[$key] = (int) $role->id;
        }

        // 3 мерчанта
        if (isset($roleIds['merchant'])) {
            for ($i = 1; $i <= 3; $i++) {
                // Генерируем уникальный логин
                do {
                    $login = $words[array_rand($words)] . random_int(1, 999999);
                } while (User::where('email', strtolower($login))->exists());
                services()->user()->create(new UserCreateDTO(
                    login: $login,
                    password: 'password',
                    role_id: $roleIds['merchant'],
                ));
            }
        }

        // 10 трейдеров
        if (isset($roleIds['trader'])) {
            for ($i = 1; $i <= 10; $i++) {
                // Генерируем уникальный логин
                do {
                    $login = $words[array_rand($words)] . random_int(1, 999999);
                } while (User::where('email', strtolower($login))->exists());
                services()->user()->create(new UserCreateDTO(
                    login: $login,
                    password: 'password',
                    role_id: $roleIds['trader'],
                ));
            }
        }

        // 1 саппорт
        if (isset($roleIds['support'])) {
            // Генерируем уникальный логин
            do {
                $login = $words[array_rand($words)] . random_int(1, 999999);
            } while (User::where('email', strtolower($login))->exists());
            services()->user()->create(new UserCreateDTO(
                login: $login,
                password: 'password',
                role_id: $roleIds['support'],
            ));
        }

        // Устанавливаем is_online = 1 для всех трейдеров и администраторов
        User::query()
            ->role(['Trader', 'Super Admin'])
            ->update(['is_online' => 1]);

        // Этап 2. Создание устройств для всех пользователей с трейдерским функционалом
        // (включая администраторов, т.к. у них есть трейдерский функционал)
        $androidDeviceNames = [
            'Samsung Galaxy A51',
            'Xiaomi Redmi Note 9',
            'Huawei P30 Lite',
            'Google Pixel 4a',
            'OnePlus Nord N10',
            'Samsung Galaxy S10e',
            'Xiaomi Mi 9T',
            'Realme 7',
            'Motorola Moto G7',
            'Nokia 7.2',
        ];

        // Собираем всех пользователей с ролями Trader и Super Admin
        $eligibleUsers = User::query()
            ->role(['Trader', 'Super Admin'])
            ->get();

        foreach ($eligibleUsers as $eligibleUser) {
            // Пропускаем, если у пользователя уже есть устройство(а)
            $hasDevice = UserDevice::query()->where('user_id', $eligibleUser->id)->exists();
            if ($hasDevice) {
                continue;
            }

            $deviceName = $androidDeviceNames[array_rand($androidDeviceNames)];
            services()->device()->create($eligibleUser->id, $deviceName);
        }

        // Этап 3. Создание платежных реквизитов: 5 на пользователя (3 карты, 2 телефона)
        $this->info('Создаю реквизиты для трейдеров и администраторов...');

        $eligibleUsers = User::query()
            ->role(['Trader', 'Super Admin'])
            ->get();

        // Выбор активных шлюзов, поддерживающих нужные типы реквизитов
        $activeGateways = queries()->paymentGateway()->getAllActive();
        $rubGateways = $activeGateways->filter(fn($pg) => strtolower($pg->currency->getCode()) === 'rub');
        $cardGateways = $rubGateways->filter(fn($pg) => in_array(DetailType::CARD, $pg->detail_types ?? []))->pluck('id')->values()->all();
        $phoneGateways = $rubGateways->filter(fn($pg) => in_array(DetailType::PHONE, $pg->detail_types ?? []))->pluck('id')->values()->all();

        foreach ($eligibleUsers as $user) {
            $userDeviceId = UserDevice::where('user_id', $user->id)->value('id');

            if (! $userDeviceId) {
                continue;
            }

            // 3 карты: 2 активные, 1 выключена
            for ($i = 0; $i < 3; $i++) {
                if (empty($cardGateways)) break;
                $isActive = $i < 2; // первые две активные
                // генерируем уникальную карту
                do {
                    $cardNumber = self::generateMirCard();
                } while (\App\Models\PaymentDetail::where('detail', $cardNumber)->exists());
                $dailyLimit = self::randomDailyLimitRub();

                $dto = new PaymentDetailCreateDTO(
                    name: 'Реквизит карты',
                    detail: $cardNumber,
                    detail_type: DetailType::CARD,
                    initials: 'Иван Иванов',
                    is_active: $isActive,
                    daily_limit: $dailyLimit,
                    currency: 'rub',
                    payment_gateway_ids: [ $cardGateways[array_rand($cardGateways)] ],
                    max_pending_orders_quantity: 100,
                    order_interval_minutes: null,
                    user_device_id: $userDeviceId,
                    user_id: $user->id,
                );
                services()->paymentDetail()->create($dto);
            }

            // 2 телефона: 1 активен, 1 отключен
            for ($i = 0; $i < 2; $i++) {
                if (empty($phoneGateways)) break;
                $isActive = $i === 0; // первый включен, второй выключен
                // генерируем уникальный номер телефона
                do {
                    $phone = self::generateRuMobile();
                } while (\App\Models\PaymentDetail::where('detail', $phone)->exists());
                $dailyLimit = self::randomDailyLimitRub();

                $dto = new PaymentDetailCreateDTO(
                    name: 'Телефон для переводов',
                    detail: $phone,
                    detail_type: DetailType::PHONE,
                    initials: 'Иван Иванов',
                    is_active: $isActive,
                    daily_limit: $dailyLimit,
                    currency: 'rub',
                    payment_gateway_ids: [ $phoneGateways[array_rand($phoneGateways)] ],
                    max_pending_orders_quantity: 100,
                    order_interval_minutes: null,
                    user_device_id: $userDeviceId,
                    user_id: $user->id,
                );
                services()->paymentDetail()->create($dto);
            }
        }

        // Этап 4. Начисление депозитов в USDT (TRC20) трейдерам и администраторам
        $this->info('Начисляю тестовые депозиты в USDT (TRC20)...');

        // используем тех же пользователей с ролями Trader и Super Admin
        foreach ($eligibleUsers as $user) {
            if (! $user->wallet) {
                continue;
            }

            $amountUsd = self::randomUsdtAmount();
            $transactionId = self::generateTransactionId();
            $txHash = self::generateTronTxHash();

            services()->invoice()->deposit(
                walletID: $user->wallet->id,
                amount: Money::fromPrecision($amountUsd, Currency::USDT()),
                balanceType: BalanceType::TRUST,
                transactionID: (string) $transactionId,
                txHash: $txHash,
            );
        }

        $this->info('Генерация тестовых данных завершена!');

        return Command::SUCCESS;
    }

    private static function generateRuMobile(): string
    {
        // Российский мобильный номер: начинается с 79 и ещё 9 цифр (итого 11)
        return '79' . str_pad((string) random_int(0, 999999999), 9, '0', STR_PAD_LEFT);
    }

    private static function generateMirCard(): string
    {
        // Сгенерируем валидный по Луну номер карты, начинающийся на 2 (МИР)
        $prefix = '2200';
        $length = 16;
        $base = $prefix;
        while (strlen($base) < $length - 1) {
            $base .= (string) random_int(0, 9);
        }

        $checksum = self::luhnChecksumDigit($base);
        return $base . $checksum;
    }

    private static function randomDailyLimitRub(): int
    {
        // от 10_000 до 100_000 с шагом 10_000
        $steps = range(1, 10);
        return $steps[array_rand($steps)] * 10000;
    }

    private static function luhnChecksumDigit(string $number): string
    {
        $sum = 0;
        $alt = true;
        for ($i = strlen($number) - 1; $i >= 0; $i--) {
            $n = (int) $number[$i];
            if ($alt) {
                $n *= 2;
                if ($n > 9) $n -= 9;
            }
            $sum += $n;
            $alt = ! $alt;
        }
        $digit = (10 - ($sum % 10)) % 10;
        return (string) $digit;
    }

    private static function randomUsdtAmount(): int
    {
        // от 2000 до 5000 с шагом 500 (в долларах США)
        $options = range(2000, 5000, 500);
        return $options[array_rand($options)];
    }

    private static function generateTransactionId(): int
    {
        // Эмуляция числового идентификатора транзакции
        return random_int(1_000_000, 999_999_999);
    }

    private static function generateTronTxHash(): string
    {
        // Эмуляция TRON tx hash: 64-символьная hex-строка
        return bin2hex(random_bytes(32));
    }
}
