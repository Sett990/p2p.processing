<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cascade_deals', function (Blueprint $table) {
            $table->id();
            
            // Идентификаторы
            $table->uuid('uuid')->unique();
            $table->string('external_id')->nullable();
            
            // Связи
            $table->foreignId('merchant_id')->constrained('merchants')->cascadeOnDelete();
            $table->foreignId('merchant_client_id')->nullable()->constrained('merchant_clients')->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            
            // Суммы и экономика (все денежные суммы хранятся как строки)
            $table->string('amount')->nullable(); // Текущая сумма сделки
            $table->string('initial_amount')->nullable(); // Изначальная сумма при создании
            $table->string('currency')->nullable(); // Валюта сделки (RUB, USD и т.д.)
            $table->string('trader_debit')->nullable(); // Сумма списания у трейдера в USDT
            $table->string('provider_cost')->nullable(); // Себестоимость у провайдера в USDT
            $table->string('profit')->nullable(); // Прибыль сервиса в USDT = trader_debit - provider_cost
            
            // Курс и рынок
            $table->string('market')->nullable(); // Рынок (bybit, binance, rapira)
            $table->string('conversion_price')->nullable(); // Курс обмена
            $table->timestamp('rate_fixed_at')->nullable(); // Дата фиксации курса
            
            // Статусы
            $table->string('status')->nullable(); // Статус сделки (pending, success, fail)
            $table->string('sub_status')->nullable(); // Подстатус сделки
            
            // Провайдер
            $table->string('selected_provider')->nullable(); // Код провайдера-победителя
            $table->unsignedBigInteger('selected_transaction_id')->nullable(); // ID победившей транзакции (foreign key добавим позже)
            
            // Детали сделки
            $table->string('payment_method')->nullable(); // Метод оплаты (c2c, card и т.д.)
            $table->json('gateway')->nullable(); // Данные шлюза (code, name, logo_link)
            $table->json('details')->nullable(); // Детали платежа (type, initials, value)
            
            // Callback
            $table->longText('callback_url')->nullable(); // URL для callback'ов мерчанту
            
            // Даты
            $table->timestamp('finished_at')->nullable(); // Дата завершения сделки
            $table->timestamps();
            
            // Индексы
            $table->index('uuid');
            $table->index('external_id');
            $table->index(['merchant_id', 'status']);
            $table->index(['merchant_id', 'created_at']);
            $table->index('status');
            $table->index('selected_provider');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cascade_deals');
    }
};
