<?php

namespace App\Services\Order\BusinesLogic;

use App\Services\Money\Money;

class Profits
{
    public static function calculate(Money $amount, Money $exchangeRate, float $totalCommissionRate, float $traderCommissionRate)
    {
        // amount = 1000р
        // exchangeRate = 100р (за 1$)
        // totalCommissionRate = 10%
        // $traderCommissionRate = 7%

        if ($totalCommissionRate < $traderCommissionRate) {
            throw new \Exception("The total commission cannot be less than the trader's commission.");
        }

        // Рассчитываем комиссию сервиса
        $serviceCommissionRate = $totalCommissionRate - $traderCommissionRate; // 10% - 7% = 3%

        // Конвертируем сумму по обменному курсу
        $totalProfit = $amount->div($exchangeRate); // 1000р / 100р = 10$

        // Рассчитываем общую сумму комиссии
        $totalCommissionAmount = $totalProfit->mul($totalCommissionRate / 100); // 10$ * 10% = 1$

        // Вычисляем чистую прибыль мерчанта
        $merchantProfit = $totalProfit->sub($totalCommissionAmount); // 10$ - 1$

        // Разделяем комиссии между сервисом и трейдером
        $serviceProfit = $totalCommissionAmount->mul($serviceCommissionRate / $totalCommissionRate);
        $traderProfit = $totalCommissionAmount->mul($traderCommissionRate / $totalCommissionRate);

        return (object) [
            'totalProfit' => $totalProfit,
            'merchantProfit' => $merchantProfit,
            'serviceProfit' => $serviceProfit,
            'traderProfit' => $traderProfit,
        ];
    }
}
