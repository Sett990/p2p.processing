<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use Database\Factories\InvoiceDataFactory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RandomizeInvoices extends Command
{
    /**
     * Имя и сигнатура консольной команды
     *
     * @var string
     */
    protected $signature = 'app:randomize-invoices 
                           {--batch=50 : Размер пакета для обработки}
                           {--memory-limit=512M : Лимит памяти для выполнения команды}';

    /**
     * Описание консольной команды
     *
     * @var string
     */
    protected $description = 'Заменяет external_id, address, tx_hash и transaction_id инвойсов на случайные значения';

    /**
     * Выполнение консольной команды
     */
    public function handle(): int
    {
        // Устанавливаем лимит памяти
        $memoryLimit = $this->option('memory-limit');
        ini_set('memory_limit', $memoryLimit);
        
        // Получаем размер пакета из опций
        $batchSize = (int)$this->option('batch');
        
        // Получаем общее количество инвойсов для обновления
        $totalCount = Invoice::count();
        
        if ($totalCount === 0) {
            $this->error('Инвойсов не найдено');
            return Command::FAILURE;
        }

        $this->info("Найдено {$totalCount} инвойсов для обновления");
        $this->info("Размер пакета: {$batchSize}");
        $this->info("Лимит памяти: {$memoryLimit}");
        
        $bar = $this->output->createProgressBar($totalCount);
        $bar->start();
        
        // Обрабатываем по частям для оптимизации использования памяти
        $processedCount = 0;
        $failedCount = 0;
        
        // Используем прямые запросы к БД для экономии памяти
        $lastId = 0;
        $processing = true;
        
        while ($processing) {
            // Начинаем транзакцию для текущего пакета
            DB::beginTransaction();
            
            try {
                // Получаем текущий пакет ID записей
                $ids = DB::table('invoices')
                    ->where('id', '>', $lastId)
                    ->orderBy('id')
                    ->limit($batchSize)
                    ->pluck('id');
                
                if ($ids->isEmpty()) {
                    $processing = false;
                    DB::commit();
                    continue;
                }
                
                // Обновляем последний обработанный ID
                $lastId = $ids->last();
                
                // Получаем записи инвойсов по ID
                $invoices = Invoice::whereIn('id', $ids)->get();
                
                foreach ($invoices as $invoice) {
                    // Получаем случайные данные для инвойса, учитывая его сеть
                    $invoiceData = InvoiceDataFactory::getRandomInvoiceData($invoice->network);
                    
                    // Обновляем информацию об инвойсе
                    $invoice->external_id = $invoiceData['external_id'];
                    $invoice->address = $invoiceData['address'];
                    $invoice->tx_hash = $invoiceData['tx_hash'];
                    $invoice->transaction_id = $invoiceData['transaction_id'];
                    $invoice->save();
                    
                    $processedCount++;
                    $bar->advance();
                }
                
                // Освобождаем память
                unset($invoices);
                
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                $failedCount++;
                
                $this->newLine();
                $this->warn("Ошибка при обработке пакета: " . $e->getMessage());
                
                // Если слишком много ошибок, прерываем выполнение
                if ($failedCount > 5) {
                    $this->error('Слишком много ошибок, прерываем выполнение');
                    return Command::FAILURE;
                }
                
                // Продолжаем со следующего пакета
                $this->info("Продолжаем со следующего пакета...");
            }
            
            // Принудительно очищаем память
            if (function_exists('gc_collect_cycles')) {
                gc_collect_cycles();
            }
        }
        
        $bar->finish();
        
        $this->newLine();
        $this->info("Обработано {$processedCount} из {$totalCount} инвойсов");
        
        if ($processedCount === $totalCount) {
            $this->info('Все данные инвойсов успешно обновлены!');
            return Command::SUCCESS;
        } else {
            $this->warn("Обновлено только {$processedCount} из {$totalCount} инвойсов");
            return Command::FAILURE;
        }
    }
} 