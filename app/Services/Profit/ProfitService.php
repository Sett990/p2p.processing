<?php

namespace App\Services\Profit;

use App\Contracts\ProfitServiceContract;
use App\Services\Money\Currency;
use App\Services\Money\Money;

class ProfitService implements ProfitServiceContract
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
    ): object {
        $teamLeaderCommissionRate = $teamLeaderCommissionRate ?? 0.0;
        $this->validateRates($totalCommissionRate, $traderCommissionRate, $teamLeaderCommissionRate);
        if ($teamLeaderCommissionRate > 0 && $teamLeaderSplitFromService === null) {
            throw new \InvalidArgumentException('Split source is required when team leader commission is set.');
        }

        $totalProfit = $this->convertToUsdt($amount, $exchangeRate);
        $totalFee = $totalProfit->mul($totalCommissionRate / 100);
        $traderFeeBase = $totalCommissionRate > 0
            ? $totalFee->mul($traderCommissionRate / $totalCommissionRate)
            : Money::zero($totalFee->getCurrency()->getCode());
        $teamLeaderFee = $totalCommissionRate > 0 && $teamLeaderCommissionRate > 0
            ? $totalFee->mul($teamLeaderCommissionRate / $totalCommissionRate)
            : Money::zero($totalFee->getCurrency()->getCode());
        $serviceFeeBase = $totalFee->sub($traderFeeBase)->abs();

        [$traderProfit, $teamLeaderProfit, $serviceProfit, $teamLeaderSplitFromServiceApplied, $teamLeaderSplitFromTrader] = $this->applyTeamLeadSplitMoney(
            totalFee: $totalFee,
            traderFeeBase: $traderFeeBase,
            teamLeaderFee: $teamLeaderFee,
            serviceFeeBase: $serviceFeeBase,
            teamLeaderSplitFromService: $teamLeaderSplitFromService
        );

        $merchantProfit = $totalProfit->sub($totalFee);

        return (object) [
            'totalProfit' => $totalProfit,
            'merchantProfit' => $merchantProfit,
            'serviceProfit' => $serviceProfit,
            'traderProfit' => $traderProfit,
            'teamLeaderProfit' => $teamLeaderProfit,
            'totalFee' => $totalFee,
            'traderFeeBase' => $traderFeeBase,
            'serviceFeeBase' => $serviceFeeBase,
            'teamLeaderSplitFromService' => $teamLeaderSplitFromServiceApplied,
            'teamLeaderSplitFromTrader' => $teamLeaderSplitFromTrader,
        ];
    }

    /**
     * Выплаты: OUT_BODY с явным конвертом в USDT и распределением комиссий.
     */
    public function calculateOutBody(
        Money $amountFiat,
        Money $conversionPrice,
        float $totalCommissionRate,
        float $traderCommissionRate,
        ?float $teamLeaderCommissionRate = null,
        ?Money $teamLeaderSplitFromService = null
    ): object {
        $teamLeaderCommissionRate = $teamLeaderCommissionRate ?? 0.0;
        if ($teamLeaderCommissionRate > 0 && $teamLeaderSplitFromService === null) {
            throw new \InvalidArgumentException('Split source is required when team leader commission is set.');
        }

        $usdtBody = $this->convertToUsdt($amountFiat, $conversionPrice);
        $totalFee = $usdtBody->mul($this->rateFraction($totalCommissionRate));
        $traderFeeBase = $totalCommissionRate > 0
            ? $totalFee->mul($this->rateFraction($traderCommissionRate, $totalCommissionRate))
            : Money::zero(Currency::USDT()->getCode());
        $teamLeaderFee = $totalCommissionRate > 0 && $teamLeaderCommissionRate > 0
            ? $totalFee->mul($this->rateFraction($teamLeaderCommissionRate, $totalCommissionRate))
            : Money::zero(Currency::USDT()->getCode());
        $serviceFeeBase = $totalFee->sub($traderFeeBase)->abs();

        [$traderFee, $teamLeaderFee, $serviceFee, $teamLeaderSplitFromServiceApplied, $teamLeaderSplitFromTrader] = $this->applyTeamLeadSplitMoney(
            totalFee: $totalFee,
            traderFeeBase: $traderFeeBase,
            teamLeaderFee: $teamLeaderFee,
            serviceFeeBase: $serviceFeeBase,
            teamLeaderSplitFromService: $teamLeaderSplitFromService
        );

        $merchantDebit = $usdtBody->add($totalFee);
        $traderCredit = $usdtBody->add($traderFee);
        $serviceRate = max($totalCommissionRate - $traderCommissionRate, 0);

        return (object) [
            'usdtBody' => $usdtBody,
            'totalFee' => $totalFee,
            'traderFee' => $traderFee,
            'teamLeaderFee' => $teamLeaderFee,
            'serviceFee' => $serviceFee,
            'traderFeeBase' => $traderFeeBase,
            'serviceFeeBase' => $serviceFeeBase,
            'teamLeaderSplitFromService' => $teamLeaderSplitFromServiceApplied,
            'teamLeaderSplitFromTrader' => $teamLeaderSplitFromTrader,
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

        if ($totalCommissionRate < $traderCommissionRate) {
            throw new \Exception('The total commission cannot be less than trader commission.');
        }

        if ($teamLeaderCommissionRate > $totalCommissionRate) {
            throw new \Exception('The team leader commission cannot exceed total commission.');
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

    /**
     * Денежный сплит тимлида: часть платит сервис, остаток — трейдер.
     *
     * @return array{0: Money, 1: Money, 2: Money, 3: ?Money, 4: ?Money} traderFee, teamLeaderFee, serviceFee, tlFromService, tlFromTrader
     */
    private function applyTeamLeadSplitMoney(
        Money $totalFee,
        Money $traderFeeBase,
        Money $teamLeaderFee,
        Money $serviceFeeBase,
        ?Money $teamLeaderSplitFromService
    ): array {
        if ($teamLeaderSplitFromService === null) {
            if ($teamLeaderFee->greaterThanZero()) {
                throw new \InvalidArgumentException('Split source is required when team leader commission is set.');
            }
            return [$traderFeeBase, $teamLeaderFee, $serviceFeeBase, null, null];
        }

        if ($teamLeaderSplitFromService->getCurrency()->notEquals($totalFee->getCurrency())) {
            throw new \InvalidArgumentException('Team leader split currency must match total fee currency.');
        }

        if ($teamLeaderSplitFromService->lessThanZero()) {
            throw new \InvalidArgumentException('Team leader split from service must be non-negative.');
        }

        if ($teamLeaderSplitFromService->greaterThan($teamLeaderFee)) {
            throw new \InvalidArgumentException('Team leader split from service cannot exceed team leader fee.');
        }

        if ($teamLeaderSplitFromService->greaterThan($serviceFeeBase)) {
            throw new \InvalidArgumentException('Team leader split from service cannot exceed service fee.');
        }

        $teamLeaderSplitFromTrader = $teamLeaderFee->sub($teamLeaderSplitFromService);

        if ($teamLeaderSplitFromTrader->greaterThan($traderFeeBase)) {
            throw new \InvalidArgumentException('Team leader split from trader cannot exceed trader fee.');
        }

        $traderFee = $traderFeeBase->sub($teamLeaderSplitFromTrader);
        $serviceFee = $serviceFeeBase->sub($teamLeaderSplitFromService);

        return [$traderFee, $teamLeaderFee, $serviceFee, $teamLeaderSplitFromService, $teamLeaderSplitFromTrader];
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
