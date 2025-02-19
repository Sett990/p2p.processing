<?php

namespace App\Http\Controllers;

use App\Enums\BalanceType;
use App\Enums\InvoiceStatus;
use App\Enums\InvoiceType;
use App\Enums\OrderStatus;
use App\Models\Invoice;
use App\Models\Order;
use App\Services\Money\Currency;
use App\Services\Money\Money;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MainPageController extends Controller
{
    public function merchant()
    {
        $query = Order::query()
            ->whereRelation('merchant', 'user_id', auth()->id())
            ->where('status', OrderStatus::SUCCESS);

        $totalProfit = Money::fromUnits($query->clone()->sum('merchant_profit'), Currency::USDT());

        $totalWithdrawalAmount = Invoice::query()
            ->whereRelation('wallet', 'user_id', auth()->id())
            ->where('type', InvoiceType::WITHDRAWAL)
            ->where('balance_type', BalanceType::MERCHANT)
            ->where('status', InvoiceStatus::SUCCESS)
            ->sum('amount');
        $totalWithdrawalAmount = Money::fromUnits($totalWithdrawalAmount, Currency::USDT());

        $balance = services()->wallet()->getTotalAvailableBalance(auth()->user()->wallet, BalanceType::MERCHANT);

        $successOrderCount = $query->clone()->count();

        //=====

        // Получаем текущий месяц и год
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        // Запрос для получения суммы доходов по дням
        $earningsByDay = Order::where('status', OrderStatus::SUCCESS)
            ->whereRelation('merchant', 'user_id', auth()->id())
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->selectRaw('DATE(created_at) as date, SUM(merchant_profit) as total_earnings')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Формируем данные для графика
        $labels = [];
        $data = [];

        // Определяем текущую дату и дату 30 дней назад
        $startDate = now()->subDays(29); // Дата 30 дней назад (включая текущий день)

        // Заполняем данные для каждого из последних 30 дней
        for ($i = 0; $i < 30; $i++) {
            $date = $startDate->copy()->addDays($i);
            $labels[] = $date->day; // Форматируем дату для отображения
            $data[] = $earningsByDay->firstWhere('date', $date->toDateString())->total_earnings ?? 0;
        }

        return Inertia::render('MainPage/Merchant/Index', [
            'statistics' => [
                'totalProfit' => $totalProfit->toBeauty(),
                'totalWithdrawalAmount' => $totalWithdrawalAmount->toBeauty(),
                'balance' => $balance->toBeauty(),
                'successOrderCount' => $successOrderCount,
            ],
            'chart' => [
                'labels' => $labels,
                'data' => $data,
            ]
        ]);
    }
}
