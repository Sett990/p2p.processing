<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\DTO\User\UserCreateDTO;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use App\Models\User;
use App\Models\UserDevice;

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

        $this->info('Генерация тестовых данных завершена!');

        return Command::SUCCESS;
    }
}
