<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
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

        // Получение идентификаторов активных реквизитов для использования в запросах
        $activePaymentDetailIds = PaymentDetail::query()
            ->whereNull('archived_at')
            ->where('is_active', true)
            ->whereRelation('user', 'is_online', true)
            ->pluck('id');

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

        // Получение суммы активных заказов (в статусе PENDING) по каждой валюте
        $pendingOrderAmounts = Order::query()
            ->whereIn('payment_detail_id', $activePaymentDetailIds)
            ->where('status', OrderStatus::PENDING)
            ->select('currency', DB::raw('SUM(CAST(amount AS DECIMAL)) as total_amount'))
            ->groupBy('currency')
            ->get()
            ->mapWithKeys(function (Order $item) {
                return [$item->currency->getCode() => $item->total_amount];
            });

        // Расчет потенциального лимита (свободный лимит - сумма активных заказов)
        $potentialLimits = PaymentDetail::query()
            ->whereNull('archived_at')
            ->where('is_active', true)
            ->whereRelation('user', 'is_online', true)
            ->select(
                'currency',
                DB::raw('SUM(CAST(daily_limit AS DECIMAL) - CAST(current_daily_limit AS DECIMAL)) as total_free_limit')
            )
            ->groupBy('currency')
            ->get()
            ->map(function ($item) use ($pendingOrderAmounts) {
                $currency = new Currency($item->currency);
                $pendingAmount = isset($pendingOrderAmounts[$item->currency->getCode()])
                    ? $pendingOrderAmounts[$item->currency->getCode()]
                    : 0;

                // Вычисляем потенциальный лимит
                $potentialLimit = $item->total_free_limit + $pendingAmount;

                return [
                    'code' => $currency->getCode(),
                    'name' => $currency->getName(),
                    'symbol' => $currency->getSymbol(),
                    'total_potential_limit' => number_format($potentialLimit / 100, 2, '.', ' ')
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
                'potentialLimits' => $potentialLimits,
                'availableCurrencies' => $availableCurrencies
            ]
        ]);
    }
}
