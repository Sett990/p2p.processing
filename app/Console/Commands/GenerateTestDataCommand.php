<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

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

        // TODO: Здесь будет логика генерации тестовых данных
        // Пока что это заготовка для будущей работы

        $this->info('Генерация тестовых данных завершена!');

        return Command::SUCCESS;
    }
}
