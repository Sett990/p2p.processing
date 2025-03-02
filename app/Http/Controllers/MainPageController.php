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
use Inertia\Inertia;

class MainPageController extends Controller
{
    public function merchant()
    {
        $stats = cache()->remember('merchant-main-page-stats-'.auth()->id(), 60 * 5, function () {
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
                $data[] = Money::fromUnits($earningsByDay->firstWhere('date', $date->toDateString())->total_earnings ?? 0, Currency::USDT())->toInt();
            }

        return [
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
            ];
        });

        return Inertia::render('MainPage/Merchant/Index', $stats);
    }

    public function trader()
{
    $stats = cache()->remember('trader-main-page-stats-'.auth()->id(), 60 * 5, function () {
        $query = Order::query()
            ->whereRelation('paymentDetail', 'user_id', auth()->id())
            ->where('status', OrderStatus::SUCCESS);

        $totalTurnover = Money::fromUnits($query->clone()->sum('total_profit'), Currency::USDT());
        $totalProfit = Money::fromUnits($query->clone()->sum('trader_profit'), Currency::USDT());

        $balance = services()->wallet()->getTotalAvailableBalance(auth()->user()->wallet, BalanceType::TRUST);

        $successOrderCount = $query->clone()->count();

        //=====

        // Получаем текущий месяц и год
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        // Запрос для получения суммы доходов по дням
        $earningsByDay = Order::where('status', OrderStatus::SUCCESS)
            ->whereRelation('paymentDetail', 'user_id', auth()->id())
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->selectRaw('DATE(created_at) as date, SUM(trader_profit) as total_earnings')
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
            $data[] = Money::fromUnits($earningsByDay->firstWhere('date', $date->toDateString())->total_earnings ?? 0, Currency::USDT())->toInt();
        }

        return [
            'statistics' => [
                'totalTurnover' => $totalTurnover->toBeauty(),
                'totalProfit' => $totalProfit->toBeauty(),
                'balance' => $balance->toBeauty(),
                'successOrderCount' => $successOrderCount,
            ],
            'chart' => [
                'labels' => $labels,
                'data' => $data,
            ]
        ];
    });

    return Inertia::render('MainPage/Trader/Index', $stats);
}

    public function admin()
    {
        $stats = cache()->remember('admin-main-page-stats-'.auth()->id(), 60 * 5, function () {
            $query = Order::query()
                ->where('status', OrderStatus::SUCCESS);

            $totalTurnover = Money::fromUnits($query->clone()->sum('total_profit'), Currency::USDT());
            $totalProfit = Money::fromUnits($query->clone()->sum('service_profit'), Currency::USDT());

            $successOrderCount = $query->clone()->count();

            //=====

            // Получаем текущий месяц и год
            $startOfMonth = now()->startOfMonth();
            $endOfMonth = now()->endOfMonth();

            // Запрос для получения суммы доходов по дням
            $earningsByDay = Order::where('status', OrderStatus::SUCCESS)
                ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->selectRaw('DATE(created_at) as date, SUM(service_profit) as total_earnings')
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
                $data[] = Money::fromUnits($earningsByDay->firstWhere('date', $date->toDateString())->total_earnings ?? 0, Currency::USDT())->toInt();
            }

            return [
                'statistics' => [
                    'totalTurnover' => $totalTurnover->toBeauty(),
                    'totalProfit' => $totalProfit->toBeauty(),
                    'successOrderCount' => $successOrderCount,
                ],
                'chart' => [
                    'labels' => $labels,
                    'data' => $data,
                ]
            ];
        });

        return Inertia::render('MainPage/Admin/Index', $stats);
    }
}
