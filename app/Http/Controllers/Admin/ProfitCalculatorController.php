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
        $teamLeaderSplitFromServicePercent = $validated['teamleader_split_from_service_percent'] ?? null;

        try {
            $calc = match ($logic) {
                'in_body' => $profitService->calculateInBody(
                    amount: $amount,
                    exchangeRate: $exchangeRate,
                    totalCommissionRate: $totalCommissionRate,
                    traderCommissionRate: $traderCommissionRate,
                    teamLeaderCommissionRate: $teamLeaderCommissionRate,
                    teamLeaderSplitFromServicePercent: $teamLeaderSplitFromServicePercent
                ),
                'out_body' => $profitService->calculateOutBody(
                    amountFiat: $amount,
                    conversionPrice: $exchangeRate,
                    totalCommissionRate: $totalCommissionRate,
                    traderCommissionRate: $traderCommissionRate,
                    teamLeaderCommissionRate: $teamLeaderCommissionRate,
                    teamLeaderSplitFromServicePercent: $teamLeaderSplitFromServicePercent
                ),
            };
        } catch (\Throwable $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 422);
        }

        $totalProfit = $this->getCalcMoney($calc, 'totalProfit')
            ?? $this->getCalcMoney($calc, 'usdtBody');
        $merchantProfit = $this->getCalcMoney($calc, 'merchantProfit')
            ?? $this->getCalcMoney($calc, 'merchantDebit');
        $serviceProfit = $this->getCalcMoney($calc, 'serviceProfit')
            ?? $this->getCalcMoney($calc, 'serviceFee');
        $traderProfit = $this->getCalcMoney($calc, 'traderProfit')
            ?? $this->getCalcMoney($calc, 'traderFee');
        $teamLeaderProfit = $this->getCalcMoney($calc, 'teamLeaderProfit')
            ?? $this->getCalcMoney($calc, 'teamLeaderFee');

        return response()->json([
            'success' => true,
            'data' => [
                'outputs' => [
                    'total_profit' => $this->formatMoney($totalProfit),
                    'merchant_profit' => $this->formatMoney($merchantProfit),
                    'service_profit' => $this->formatMoney($serviceProfit),
                    'trader_profit' => $this->formatMoney($traderProfit),
                    'teamleader_profit' => $this->formatMoney($teamLeaderProfit),
                    'total_fee' => $this->formatMoney($this->getCalcMoney($calc, 'totalFee')),
                    'trader_credit' => $this->formatMoney($this->getCalcMoney($calc, 'traderCredit')),
                    'trader_debit' => $this->formatMoney($this->getCalcMoney($calc, 'traderDebit')),
                ],
            ],
        ]);
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
