<?php

namespace App\Services\Profit;

use App\Contracts\ProfitServiceContract;
use App\Services\Money\Currency;
use App\Services\Money\Money;

class ProfitService implements ProfitServiceContract
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
    ): object {
        $teamLeaderCommissionRate = $teamLeaderCommissionRate ?? 0.0;
        $this->validateRates($totalCommissionRate, $traderCommissionRate, $teamLeaderCommissionRate);

        $totalProfit = $amount->div($exchangeRate);
        $totalFee = $totalProfit->mul($totalCommissionRate / 100);

        [$traderProfit, $teamLeaderProfit, $serviceProfit] = $this->splitTotalFee(
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
    public function calculateOutBody(
        Money $amount,
        Money $exchangeRate,
        float $totalCommissionRate,
        float $traderCommissionRate,
        ?float $teamLeaderCommissionRate = null
    ): object {
        $teamLeaderCommissionRate = $teamLeaderCommissionRate ?? 0.0;
        $this->validateRates($totalCommissionRate, $traderCommissionRate, $teamLeaderCommissionRate);

        $usdtBody = $amount->div($exchangeRate);
        $totalFee = $usdtBody->mul($totalCommissionRate / 100);

        [$traderFee, $teamLeaderFee, $serviceFee] = $this->splitTotalFee(
            totalFee: $totalFee,
            totalCommissionRate: $totalCommissionRate,
            traderCommissionRate: $traderCommissionRate,
            teamLeaderCommissionRate: $teamLeaderCommissionRate
        );

        $merchantPay = $usdtBody->add($totalFee);
        $traderReceive = $usdtBody->add($traderFee);

        return (object) [
            // Совместимые поля (как в calculateInBody):
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
    public function calculateOutRate(
        Money $amount,
        Money $exchangeRate,
        float $totalCommissionRate,
        float $traderCommissionRate,
        ?float $teamLeaderCommissionRate = null
    ): object {
        $teamLeaderCommissionRate = $teamLeaderCommissionRate ?? 0.0;
        $this->validateRates($totalCommissionRate, $traderCommissionRate, $teamLeaderCommissionRate);

        $denom = 1 + ($totalCommissionRate / 100);
        if ($denom <= 0) {
            throw new \Exception('Invalid total commission rate.');
        }

        $rateEff = $exchangeRate->div((string)$denom);

        $usdtBody = $amount->div($exchangeRate);
        $merchantPay = $amount->div($rateEff);
        $totalFee = $merchantPay->sub($usdtBody);

        [$traderFee, $teamLeaderFee, $serviceFee] = $this->splitTotalFee(
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
    public function calculateInRate(
        Money $amount,
        Money $exchangeRate,
        float $totalCommissionRate,
        float $traderCommissionRate,
        ?float $teamLeaderCommissionRate = null
    ): object {
        $teamLeaderCommissionRate = $teamLeaderCommissionRate ?? 0.0;
        $this->validateRates($totalCommissionRate, $traderCommissionRate, $teamLeaderCommissionRate);

        $mult = 1 + ($totalCommissionRate / 100);
        if ($mult <= 0) {
            throw new \Exception('Invalid total commission rate.');
        }

        $rateEff = $exchangeRate->mul((string)$mult);

        $usdtBody = $amount->div($exchangeRate);
        $merchantCredit = $amount->div($rateEff);
        $totalFee = $usdtBody->sub($merchantCredit);

        [$traderFee, $teamLeaderFee, $serviceFee] = $this->splitTotalFee(
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

    /**
     * Выплаты: OUT_BODY с явным конвертом в USDT и распределением комиссий.
     */
    public function calculatePayoutOutBody(
        Money $amountFiat,
        Money $conversionPrice,
        float $totalCommissionRate,
        float $traderCommissionRate,
        ?float $teamLeaderCommissionRate = null
    ): object {
        $teamLeaderCommissionRate = $teamLeaderCommissionRate ?? 0.0;

        $usdtBody = $this->convertToUsdt($amountFiat, $conversionPrice);
        $totalFee = $usdtBody->mul($this->rateFraction($totalCommissionRate));
        $traderFee = $totalCommissionRate > 0
            ? $totalFee->mul($this->rateFraction($traderCommissionRate, $totalCommissionRate))
            : Money::zero(Currency::USDT()->getCode());
        $teamLeaderFee = $totalCommissionRate > 0 && $teamLeaderCommissionRate > 0
            ? $totalFee->mul($this->rateFraction($teamLeaderCommissionRate, $totalCommissionRate))
            : Money::zero(Currency::USDT()->getCode());
        $serviceFee = $totalFee->sub($traderFee)->sub($teamLeaderFee)->abs();

        $merchantDebit = $usdtBody->add($totalFee);
        $traderCredit = $usdtBody->add($traderFee);
        $serviceRate = max($totalCommissionRate - $traderCommissionRate - $teamLeaderCommissionRate, 0);

        return (object) [
            'usdtBody' => $usdtBody,
            'totalFee' => $totalFee,
            'traderFee' => $traderFee,
            'teamLeaderFee' => $teamLeaderFee,
            'serviceFee' => $serviceFee,
            'merchantDebit' => $merchantDebit,
            'traderCredit' => $traderCredit,
            'serviceRate' => $serviceRate,
        ];
    }

    private function validateRates(float $totalCommissionRate, float $traderCommissionRate, float $teamLeaderCommissionRate): void
    {
        if ($totalCommissionRate < 0 || $traderCommissionRate < 0 || $teamLeaderCommissionRate < 0) {
            throw new \Exception('Commission rates must be non-negative.');
        }

        if ($totalCommissionRate < ($traderCommissionRate + $teamLeaderCommissionRate)) {
            throw new \Exception('The total commission cannot be less than trader + team leader commission.');
        }
    }

    /**
     * Делим общую комиссию по долям от totalCommissionRate.
     * Остаток (из-за precision/truncate) уходит в сервис.
     *
     * @return array{0: Money, 1: Money, 2: Money} traderFee, teamLeaderFee, serviceFee
     */
    private function splitTotalFee(
        Money $totalFee,
        float $totalCommissionRate,
        float $traderCommissionRate,
        float $teamLeaderCommissionRate
    ): array {
        if ($totalCommissionRate <= 0) {
            $zero = Money::zero($totalFee->getCurrency()->getCode());
            return [$zero, $zero, $zero];
        }

        $traderFee = $traderCommissionRate > 0
            ? $totalFee->mul($traderCommissionRate / $totalCommissionRate)
            : Money::zero($totalFee->getCurrency()->getCode());

        $teamLeaderFee = $teamLeaderCommissionRate > 0
            ? $totalFee->mul($teamLeaderCommissionRate / $totalCommissionRate)
            : Money::zero($totalFee->getCurrency()->getCode());

        $serviceFee = $totalFee->sub($traderFee)->sub($teamLeaderFee);

        return [$traderFee, $teamLeaderFee, $serviceFee];
    }

    private function convertToUsdt(Money $amountFiat, Money $conversionPrice): Money
    {
        if ($amountFiat->getCurrency()->notEquals($conversionPrice->getCurrency())) {
            throw new \InvalidArgumentException('Conversion currencies must match.');
        }

        $usdtAmount = bcdiv(
            $amountFiat->toPrecision(),
            $conversionPrice->toPrecision(),
            Money::DEFAULT_PRECISION
        );

        return Money::fromPrecision($usdtAmount, Currency::USDT()->getCode());
    }

    private function rateFraction(float $value, float $divider = 100): string
    {
        if ($divider === 0.0) {
            return '0';
        }

        $fraction = bcdiv((string) $value, (string) $divider, 10);

        return rtrim(rtrim($fraction, '0'), '.') ?: '0';
    }
}
