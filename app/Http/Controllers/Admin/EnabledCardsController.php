<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentDetail;
use App\Services\Money\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class EnabledCardsController extends Controller
{
    public function index()
    {
        // Получение общего количества включенных реквизитов
        $enabledPaymentDetailsCount = PaymentDetail::query()
            ->whereNull('archived_at')
            ->where('is_active', true)
            ->whereRelation('user', 'is_online', true)
            ->count();

        // Получение свободного лимита по каждой валюте
        $currencyLimits = PaymentDetail::query()
            ->whereNull('archived_at')
            ->where('is_active', true)
            ->whereRelation('user', 'is_online', true)
            ->select(
                'currency',
                DB::raw('SUM(CAST(daily_limit AS DECIMAL) - CAST(current_daily_limit AS DECIMAL)) as total_free_limit')
            )
            ->groupBy('currency')
            ->get()
            ->map(function ($item) {
                // Создаем объект валюты для получения правильного имени и символа
                $currency = new Currency($item->currency);
                
                return [
                    'code' => $currency->getCode(),
                    'name' => $currency->getName(),
                    'symbol' => $currency->getSymbol(),
                    'total_free_limit' => number_format($item->total_free_limit / 100, 2, '.', ' ')
                ];
            });

        // Список всех валют для селекта
        $availableCurrencies = Currency::getAll()
            ->map(function ($currency) {
                return [
                    'code' => $currency->getCode(),
                    'name' => $currency->getName(),
                    'symbol' => $currency->getSymbol()
                ];
            })
            ->values()
            ->toArray();

        return Inertia::render('EnabledCards/Index', [
            'statistics' => [
                'totalPaymentDetails' => $enabledPaymentDetailsCount,
                'currencyLimits' => $currencyLimits,
                'availableCurrencies' => $availableCurrencies
            ]
        ]);
    }
}
