<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\DTO\User\UserCreateDTO;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

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
                $login = $words[array_rand($words)] . $i;
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
                $login = $words[array_rand($words)] . $i;
                services()->user()->create(new UserCreateDTO(
                    login: $login,
                    password: 'password',
                    role_id: $roleIds['trader'],
                ));
            }
        }

        // 1 саппорт
        if (isset($roleIds['support'])) {
            $login = $words[array_rand($words)];
            services()->user()->create(new UserCreateDTO(
                login: $login,
                password: 'password',
                role_id: $roleIds['support'],
            ));
        }

        $this->info('Генерация тестовых данных завершена!');

        return Command::SUCCESS;
    }
}
