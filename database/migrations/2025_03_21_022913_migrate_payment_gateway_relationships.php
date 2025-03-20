<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Перенос данных из старого типа отношений в новый
        $paymentDetails = DB::table('payment_details')->whereNotNull('payment_gateway_id')->get();

        foreach ($paymentDetails as $paymentDetail) {
            // Добавляем запись в связующую таблицу для основного payment_gateway
            if ($paymentDetail->payment_gateway_id) {
                DB::table('payment_detail_payment_gateway')->insert([
                    'payment_detail_id' => $paymentDetail->id,
                    'payment_gateway_id' => $paymentDetail->payment_gateway_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Добавляем запись в связующую таблицу для sub_payment_gateway
            if ($paymentDetail->sub_payment_gateway_id) {
                DB::table('payment_detail_payment_gateway')->insert([
                    'payment_detail_id' => $paymentDetail->id,
                    'payment_gateway_id' => $paymentDetail->sub_payment_gateway_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // После успешного переноса данных, можно удалить старые колонки
        Schema::table('payment_details', function (Blueprint $table) {
            $table->dropForeign(['payment_gateway_id']);
            $table->dropForeign(['sub_payment_gateway_id']);
            $table->dropColumn(['payment_gateway_id', 'sub_payment_gateway_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Восстанавливаем старые колонки
        Schema::table('payment_details', function (Blueprint $table) {
            $table->foreignId('payment_gateway_id')->nullable()->constrained();
            $table->foreignId('sub_payment_gateway_id')->nullable()->constrained('payment_gateways');
        });

        // Восстанавливаем данные из связующей таблицы
        $relationships = DB::table('payment_detail_payment_gateway')->get();
        $processedDetails = [];

        foreach ($relationships as $relationship) {
            $detailId = $relationship->payment_detail_id;

            // Если это первая запись для данного payment_detail
            if (!isset($processedDetails[$detailId])) {
                DB::table('payment_details')
                    ->where('id', $detailId)
                    ->update(['payment_gateway_id' => $relationship->payment_gateway_id]);
                $processedDetails[$detailId] = $relationship->payment_gateway_id;
            }
            // Если это вторая запись, считаем её sub_payment_gateway
            else {
                DB::table('payment_details')
                    ->where('id', $detailId)
                    ->update(['sub_payment_gateway_id' => $relationship->payment_gateway_id]);
            }
        }
    }
};
