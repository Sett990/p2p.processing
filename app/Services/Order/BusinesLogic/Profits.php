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
        $amountConverted = $amount->div($exchangeRate); // 1000р / 100р = 10$

        // Рассчитываем общую сумму комиссии
        $totalCommissionAmount = $amountConverted->mul($totalCommissionRate / 100); // 10$ * 10% = 1$

        // Вычисляем чистую прибыль мерчанта
        $merchantProfit = $amountConverted->sub($totalCommissionAmount); // 10$ - 1$

        // Разделяем комиссии между сервисом и трейдером
        $serviceCommission = $totalCommissionAmount->mul($serviceCommissionRate / $totalCommissionRate);
        $traderCommission = $totalCommissionAmount->mul($traderCommissionRate / $totalCommissionRate);

        return (object) [
            'amountConverted' => $amountConverted,
            'merchantProfit' => $merchantProfit,
            'serviceCommission' => $serviceCommission,
            'traderCommission' => $traderCommission,
        ];
    }
}
