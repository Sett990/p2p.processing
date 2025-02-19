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

        // Заполняем данные для каждого дня месяца
        for ($day = 1; $day <= $endOfMonth->day; $day++) {
            $date = $startOfMonth->copy()->addDays($day - 1);
            $labels[] = $date->day;
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
