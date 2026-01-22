<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Contracts\ProfitServiceContract;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Profit\CalculateProfitRequest;
use App\Services\Money\Currency;
use App\Services\Money\Money;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class ProfitCalculatorController extends Controller
{
    /**
     * @return Response
     */
    public function index(): Response
    {
        $currencies = Currency::getAllCodes();
        $defaultCurrency = $currencies[0] ?? 'rub';

        return Inertia::render('Admin/Profit/Index', [
            'currencies' => $currencies,
            'defaults' => [
                'logic' => 'in_body',
                'amount_currency' => $defaultCurrency,
                'amount' => '1000',
                'exchange_rate' => '100',
                'total_commission_rate' => 5,
                'trader_commission_rate' => 2,
                'teamleader_commission_rate' => 0,
                'teamleader_split_from_service_percent' => null,
            ],
        ]);
    }

    /**
     * @param CalculateProfitRequest $request
     * @param ProfitServiceContract $profitService
     *
     * @return JsonResponse
     */
    public function calculate(
        CalculateProfitRequest $request,
        ProfitServiceContract $profitService
    ): JsonResponse {
        $validated = $request->validated();

        $logic = $validated['logic'];
        $amountCurrency = $validated['amount_currency'];
        $amount = Money::fromPrecision((string) $validated['amount'], $amountCurrency);
        $exchangeRate = Money::fromPrecision((string) $validated['exchange_rate'], $amountCurrency);
        $totalCommissionRate = (float) $validated['total_commission_rate'];
        $traderCommissionRate = (float) $validated['trader_commission_rate'];
        $teamLeaderCommissionRate = (float) $validated['teamleader_commission_rate'];
        $teamLeaderSplitFromService = $this->resolveTeamLeaderSplitFromService($validated, $amount, $exchangeRate);
        $teamLeaderSplitFromServicePercent = $validated['teamleader_split_from_service_percent'] ?? null;
        $teamLeaderSplitFromTraderPercent = $teamLeaderSplitFromServicePercent === null
            ? null
            : max(0, 100 - (float) $teamLeaderSplitFromServicePercent);

        try {
            $calc = match ($logic) {
                'in_body' => $profitService->calculateInBody(
                    amount: $amount,
                    exchangeRate: $exchangeRate,
                    totalCommissionRate: $totalCommissionRate,
                    traderCommissionRate: $traderCommissionRate,
                    teamLeaderCommissionRate: $teamLeaderCommissionRate,
                    teamLeaderSplitFromService: $teamLeaderSplitFromService
                ),
                'out_body' => $profitService->calculateOutBody(
                    amountFiat: $amount,
                    conversionPrice: $exchangeRate,
                    totalCommissionRate: $totalCommissionRate,
                    traderCommissionRate: $traderCommissionRate,
                    teamLeaderCommissionRate: $teamLeaderCommissionRate,
                    teamLeaderSplitFromService: $teamLeaderSplitFromService
                ),
            };
        } catch (\Throwable $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'data' => $this->buildResponse(
                logic: $logic,
                amount: $amount,
                exchangeRate: $exchangeRate,
                totalCommissionRate: $totalCommissionRate,
                traderCommissionRate: $traderCommissionRate,
                teamLeaderCommissionRate: $teamLeaderCommissionRate,
                teamLeaderSplitFromService: $teamLeaderSplitFromService,
                calc: $calc,
                teamLeaderSplitFromServicePercent: $teamLeaderSplitFromServicePercent,
                teamLeaderSplitFromTraderPercent: $teamLeaderSplitFromTraderPercent
            ),
        ]);
    }

    /**
     * @param array<string, mixed> $validated
     */
    private function resolveTeamLeaderSplitFromService(
        array $validated,
        Money $amount,
        Money $exchangeRate
    ): ?Money
    {
        $splitPercent = $validated['teamleader_split_from_service_percent'] ?? null;

        if ($splitPercent === null || $splitPercent === '') {
            return null;
        }

        $totalCommissionRate = (float) $validated['total_commission_rate'];
        $teamLeaderCommissionRate = (float) $validated['teamleader_commission_rate'];

        if ($totalCommissionRate <= 0 || $teamLeaderCommissionRate <= 0) {
            return null;
        }

        $totalProfit = $this->resolveTotalProfit($validated, $amount, $exchangeRate);
        $totalFee = $totalProfit->mul(bcdiv((string) $totalCommissionRate, '100', 10));
        $teamLeaderFee = $totalFee->mul(bcdiv((string) $teamLeaderCommissionRate, (string) $totalCommissionRate, 10));

        return $teamLeaderFee->mul(bcdiv((string) $splitPercent, '100', 10));
    }

    private function resolveTotalProfit(
        array $validated,
        Money $amount,
        Money $exchangeRate
    ): Money {
        $usdtAmount = bcdiv(
            $amount->toPrecision(),
            $exchangeRate->toPrecision(),
            Money::DEFAULT_PRECISION
        );

        return Money::fromPrecision($usdtAmount, Currency::USDT()->getCode());
    }

    /**
     * @return array<string, mixed>
     */
    private function buildResponse(
        string $logic,
        Money $amount,
        Money $exchangeRate,
        float $totalCommissionRate,
        float $traderCommissionRate,
        float $teamLeaderCommissionRate,
        ?Money $teamLeaderSplitFromService,
        object $calc,
        ?float $teamLeaderSplitFromServicePercent = null,
        ?float $teamLeaderSplitFromTraderPercent = null
    ): array {
        $totalProfit = $this->getCalcMoney($calc, 'totalProfit')
            ?? $this->getCalcMoney($calc, 'usdtBody');
        $merchantProfit = $this->getCalcMoney($calc, 'merchantProfit')
            ?? $this->getCalcMoney($calc, 'merchantDebit')
            ?? $this->getCalcMoney($calc, 'merchantCredit');
        $serviceProfit = $this->getCalcMoney($calc, 'serviceProfit')
            ?? $this->getCalcMoney($calc, 'serviceFee');
        $traderProfit = $this->getCalcMoney($calc, 'traderProfit')
            ?? $this->getCalcMoney($calc, 'traderFee');
        $teamLeaderProfit = $this->getCalcMoney($calc, 'teamLeaderProfit')
            ?? $this->getCalcMoney($calc, 'teamLeaderFee');

        return [
            'logic' => $logic,
            'inputs' => [
                'amount' => $this->formatMoney($amount),
                'exchange_rate' => $this->formatMoney($exchangeRate),
                'total_commission_rate' => $totalCommissionRate,
                'trader_commission_rate' => $traderCommissionRate,
                'teamleader_commission_rate' => $teamLeaderCommissionRate,
                'service_commission_rate' => $calc->serviceRate ?? max($totalCommissionRate - $traderCommissionRate, 0),
                'teamleader_split_from_service_percent' => $teamLeaderSplitFromServicePercent,
                'teamleader_split_from_trader_percent' => $teamLeaderSplitFromTraderPercent,
            ],
            'outputs' => [
                'total_profit' => $this->formatMoney($totalProfit),
                'merchant_profit' => $this->formatMoney($merchantProfit),
                'service_profit' => $this->formatMoney($serviceProfit),
                'trader_profit' => $this->formatMoney($traderProfit),
                'teamleader_profit' => $this->formatMoney($teamLeaderProfit),
                'total_fee' => $this->formatMoney($this->getCalcMoney($calc, 'totalFee')),
                'trader_receive' => $this->formatMoney($this->getCalcMoney($calc, 'traderReceive')),
                'trader_credit' => $this->formatMoney($this->getCalcMoney($calc, 'traderCredit')),
                'trader_debit' => $this->formatMoney($this->getCalcMoney($calc, 'traderDebit')),
            ],
            'service' => $this->buildServiceFields($calc),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildServiceFields(object $calc): array
    {
        $fields = [
            'totalProfit',
            'merchantProfit',
            'serviceProfit',
            'traderProfit',
            'teamLeaderProfit',
            'totalFee',
            'traderDebit',
            'traderReceive',
            'merchantCredit',
            'usdtBody',
            'traderFee',
            'teamLeaderFee',
            'serviceFee',
            'merchantDebit',
            'traderCredit',
        ];

        $result = [];
        foreach ($fields as $field) {
            if (! property_exists($calc, $field)) {
                continue;
            }

            $value = $calc->{$field};
            $result[$field] = $value instanceof Money
                ? $this->formatMoney($value)
                : $value;
        }

        return $result;
    }

    private function getCalcMoney(object $calc, string $property): ?Money
    {
        return property_exists($calc, $property) ? $calc->{$property} : null;
    }

    /**
     * @return array{value: string, currency: string}|null
     */
    private function formatMoney(?Money $money): ?array
    {
        if (! $money) {
            return null;
        }

        return [
            'value' => $money->toPrecision(),
            'currency' => strtoupper($money->getCurrency()->getCode()),
        ];
    }
}
