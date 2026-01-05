<?php

namespace App\Services\Order\BusinesLogic;

use App\Services\Money\Money;

class Profits
{
    /**
     * Логика #3: IN_BODY (вход + BODY) — комиссия "из тела".
     */
    public static function calculate(Money $amount, Money $exchangeRate, float $totalCommissionRate, float $traderCommissionRate, ?float $teamLeaderCommissionRate = null)
    {
        $teamLeaderCommissionRate = $teamLeaderCommissionRate ?? 0.0;
        self::validateRates($totalCommissionRate, $traderCommissionRate, $teamLeaderCommissionRate);

        $totalProfit = $amount->div($exchangeRate);
        $totalFee = $totalProfit->mul($totalCommissionRate / 100);

        [$traderProfit, $teamLeaderProfit, $serviceProfit] = self::splitTotalFee(
            totalFee: $totalFee,
            totalCommissionRate: $totalCommissionRate,
            traderCommissionRate: $traderCommissionRate,
            teamLeaderCommissionRate: $teamLeaderCommissionRate
        );

        $merchantProfit = $totalProfit->sub($totalFee);

        return (object) [
            'totalProfit' => $totalProfit,
            'merchantProfit' => $merchantProfit,
            'serviceProfit' => $serviceProfit,
            'traderProfit' => $traderProfit,
            'teamLeaderProfit' => $teamLeaderProfit,
        ];
    }

    /**
     * Логика #1: OUT_BODY (выход + BODY) — комиссия "сверху" отдельной суммой.
     *
     * Пример:
     * - usdt_body = amount / rate_base
     * - total_fee = usdt_body * total%
     * - merchantPay = usdt_body + total_fee
     * - traderReceive = usdt_body + trader_fee
     */
    public static function calculateOutBody(
        Money $amount,
        Money $exchangeRate,
        float $totalCommissionRate,
        float $traderCommissionRate,
        ?float $teamLeaderCommissionRate = null
    ) {
        $teamLeaderCommissionRate = $teamLeaderCommissionRate ?? 0.0;
        self::validateRates($totalCommissionRate, $traderCommissionRate, $teamLeaderCommissionRate);

        $usdtBody = $amount->div($exchangeRate);
        $totalFee = $usdtBody->mul($totalCommissionRate / 100);

        [$traderFee, $teamLeaderFee, $serviceFee] = self::splitTotalFee(
            totalFee: $totalFee,
            totalCommissionRate: $totalCommissionRate,
            traderCommissionRate: $traderCommissionRate,
            teamLeaderCommissionRate: $teamLeaderCommissionRate
        );

        $merchantPay = $usdtBody->add($totalFee);
        $traderReceive = $usdtBody->add($traderFee);

        return (object) [
            // Совместимые поля (как в calculate):
            'totalProfit' => $usdtBody,
            'merchantProfit' => $merchantPay,
            'serviceProfit' => $serviceFee,
            'traderProfit' => $traderFee,
            'teamLeaderProfit' => $teamLeaderFee,

            // Доп. поля для явной семантики:
            'totalFee' => $totalFee,
            'traderReceive' => $traderReceive,
        ];
    }

    /**
     * Логика #2: OUT_RATE (выход + RATE) — один расчётный курс ("курс минус total%").
     *
     * - rate_eff = rate_base / (1 + total%)
     * - merchantPay = amount / rate_eff
     * - total_fee = merchantPay - usdt_body
     */
    public static function calculateOutRate(
        Money $amount,
        Money $exchangeRate,
        float $totalCommissionRate,
        float $traderCommissionRate,
        ?float $teamLeaderCommissionRate = null
    ) {
        $teamLeaderCommissionRate = $teamLeaderCommissionRate ?? 0.0;
        self::validateRates($totalCommissionRate, $traderCommissionRate, $teamLeaderCommissionRate);

        $denom = 1 + ($totalCommissionRate / 100);
        if ($denom <= 0) {
            throw new \Exception('Invalid total commission rate.');
        }

        $rateEff = $exchangeRate->div((string)$denom);

        $usdtBody = $amount->div($exchangeRate);
        $merchantPay = $amount->div($rateEff);
        $totalFee = $merchantPay->sub($usdtBody);

        [$traderFee, $teamLeaderFee, $serviceFee] = self::splitTotalFee(
            totalFee: $totalFee,
            totalCommissionRate: $totalCommissionRate,
            traderCommissionRate: $traderCommissionRate,
            teamLeaderCommissionRate: $teamLeaderCommissionRate
        );

        $traderReceive = $usdtBody->add($traderFee);

        return (object) [
            'totalProfit' => $usdtBody,
            'merchantProfit' => $merchantPay,
            'serviceProfit' => $serviceFee,
            'traderProfit' => $traderFee,
            'teamLeaderProfit' => $teamLeaderFee,

            'effectiveRate' => $rateEff,
            'totalFee' => $totalFee,
            'traderReceive' => $traderReceive,
        ];
    }

    /**
     * Логика #4: IN_RATE (вход + RATE) — один расчётный курс ("курс плюс total%").
     *
     * - rate_eff = rate_base * (1 + total%)
     * - merchantCredit = amount / rate_eff
     * - total_fee = usdt_body - merchantCredit
     *
     * Важно: из-за обрезания по precision остаток от деления уходит в сервис.
     */
    public static function calculateInRate(
        Money $amount,
        Money $exchangeRate,
        float $totalCommissionRate,
        float $traderCommissionRate,
        ?float $teamLeaderCommissionRate = null
    ) {
        $teamLeaderCommissionRate = $teamLeaderCommissionRate ?? 0.0;
        self::validateRates($totalCommissionRate, $traderCommissionRate, $teamLeaderCommissionRate);

        $mult = 1 + ($totalCommissionRate / 100);
        if ($mult <= 0) {
            throw new \Exception('Invalid total commission rate.');
        }

        $rateEff = $exchangeRate->mul((string)$mult);

        $usdtBody = $amount->div($exchangeRate);
        $merchantCredit = $amount->div($rateEff);
        $totalFee = $usdtBody->sub($merchantCredit);

        [$traderFee, $teamLeaderFee, $serviceFee] = self::splitTotalFee(
            totalFee: $totalFee,
            totalCommissionRate: $totalCommissionRate,
            traderCommissionRate: $traderCommissionRate,
            teamLeaderCommissionRate: $teamLeaderCommissionRate
        );

        return (object) [
            'totalProfit' => $usdtBody,
            'merchantProfit' => $merchantCredit,
            'serviceProfit' => $serviceFee,
            'traderProfit' => $traderFee,
            'teamLeaderProfit' => $teamLeaderFee,

            'effectiveRate' => $rateEff,
            'totalFee' => $totalFee,
        ];
    }

    private static function validateRates(float $totalCommissionRate, float $traderCommissionRate, float $teamLeaderCommissionRate): void
    {
        if ($totalCommissionRate < 0 || $traderCommissionRate < 0 || $teamLeaderCommissionRate < 0) {
            throw new \Exception('Commission rates must be non-negative.');
        }

        if ($totalCommissionRate < ($traderCommissionRate + $teamLeaderCommissionRate)) {
            throw new \Exception("The total commission cannot be less than trader + team leader commission.");
        }
    }

    /**
     * Делим общую комиссию по долям от totalCommissionRate.
     * Остаток (из-за precision/truncate) уходит в сервис (как в примере IN_RATE).
     *
     * @return array{0: Money, 1: Money, 2: Money} traderFee, teamLeaderFee, serviceFee
     */
    private static function splitTotalFee(
        Money $totalFee,
        float $totalCommissionRate,
        float $traderCommissionRate,
        float $teamLeaderCommissionRate
    ): array {
        if ($totalCommissionRate <= 0) {
            $zero = Money::zero($totalFee->getCurrency());
            return [$zero, $zero, $zero];
        }

        $traderFee = $traderCommissionRate > 0
            ? $totalFee->mul($traderCommissionRate / $totalCommissionRate)
            : Money::zero($totalFee->getCurrency());

        $teamLeaderFee = $teamLeaderCommissionRate > 0
            ? $totalFee->mul($teamLeaderCommissionRate / $totalCommissionRate)
            : Money::zero($totalFee->getCurrency());

        // остаток — в сервис (важно для IN_RATE и в целом для стабильной суммы)
        $serviceFee = $totalFee->sub($traderFee)->sub($teamLeaderFee);

        return [$traderFee, $teamLeaderFee, $serviceFee];
    }
}
