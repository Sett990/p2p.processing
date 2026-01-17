<?php

namespace App\Contracts;

use App\Services\Money\Money;

interface ProfitServiceContract
{
    /**
     * Логика #3: IN_BODY (вход + BODY) — комиссия "из тела".
     */
    public function calculateInBody(
        Money $amount,
        Money $exchangeRate,
        float $totalCommissionRate,
        float $traderCommissionRate,
        ?float $teamLeaderCommissionRate = null
    ): object;

    /**
     * Логика #1: OUT_BODY (выход + BODY) — комиссия "сверху" отдельной суммой.
     */
    public function calculateOutBody(
        Money $amount,
        Money $exchangeRate,
        float $totalCommissionRate,
        float $traderCommissionRate,
        ?float $teamLeaderCommissionRate = null
    ): object;

    /**
     * Логика #2: OUT_RATE (выход + RATE) — один расчётный курс ("курс минус total%").
     */
    public function calculateOutRate(
        Money $amount,
        Money $exchangeRate,
        float $totalCommissionRate,
        float $traderCommissionRate,
        ?float $teamLeaderCommissionRate = null
    ): object;

    /**
     * Логика #4: IN_RATE (вход + RATE) — один расчётный курс ("курс плюс total%").
     */
    public function calculateInRate(
        Money $amount,
        Money $exchangeRate,
        float $totalCommissionRate,
        float $traderCommissionRate,
        ?float $teamLeaderCommissionRate = null
    ): object;

    /**
     * Выплаты: OUT_BODY с явным конвертом в USDT и распределением комиссий.
     */
    public function calculatePayoutOutBody(
        Money $amountFiat,
        Money $conversionPrice,
        float $totalCommissionRate,
        float $traderCommissionRate,
        ?float $teamLeaderCommissionRate = null
    ): object;
}
