<?php

namespace App\Contracts;

use App\Services\Money\Money;

interface ProfitServiceContract
{
    /**
     * Логика: IN_BODY (вход + BODY) — комиссия "из тела".
     */
    public function calculateInBody(
        Money $amount,
        Money $exchangeRate,
        float $totalCommissionRate,
        float $traderCommissionRate,
        ?float $teamLeaderCommissionRate = null,
        ?Money $teamLeaderSplitFromService = null
    ): object;

    /**
     * Логика: OUT_BODY (выход + BODY) — комиссия "сверху" отдельной суммой.
     */
    public function calculateOutBody(
        Money $amount,
        Money $exchangeRate,
        float $totalCommissionRate,
        float $traderCommissionRate,
        ?float $teamLeaderCommissionRate = null,
        ?Money $teamLeaderSplitFromService = null
    ): object;

    /**
     * Выплаты: OUT_BODY с явным конвертом в USDT и распределением комиссий.
     */
    public function calculatePayoutOutBody(
        Money $amountFiat,
        Money $conversionPrice,
        float $totalCommissionRate,
        float $traderCommissionRate,
        ?float $teamLeaderCommissionRate = null,
        ?Money $teamLeaderSplitFromService = null
    ): object;
}
