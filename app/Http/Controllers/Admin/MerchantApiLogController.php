<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\MerchantApiLogResource;
use App\Models\MerchantApiRequestLog;
use App\Models\PaymentGateway;
use App\Services\Money\Currency;
use Inertia\Inertia;

class MerchantApiLogController extends Controller
{
    public function index()
    {
        $filters = $this->getTableFilters();
        $filtersVariants = $this->getApiLogFiltersData();

        $logs = queries()->merchantApiLog()->paginateForAdmin($filters);

        // Получаем все статистические данные из кэша или вычисляем их
        $cacheKey = 'merchant_api_logs_statistics';
        $statistics = cache()->remember($cacheKey, now()->addMinutes(5), function () {
            // Подсчет количества неудачных запросов за сегодня
            $failedToday = MerchantApiRequestLog::query()
                ->where('is_successful', false)
                ->whereDate('created_at', now()->toDateString())
                ->count();

            // Подсчет количества успешных запросов за сегодня
            $successToday = MerchantApiRequestLog::query()
                ->where('is_successful', true)
                ->whereDate('created_at', now()->toDateString())
                ->count();

            // Подсчет общего количества неудачных запросов за все время
            $failedTotal = MerchantApiRequestLog::query()
                ->where('is_successful', false)
                ->count();

            // Подсчет общего количества успешных запросов за все время
            $successTotal = MerchantApiRequestLog::query()
                ->where('is_successful', true)
                ->count();

            // Подсчет суммы успешных запросов за сегодня по валютам
            $sumBySuccessCurrencyToday = MerchantApiRequestLog::query()
                ->where('is_successful', true)
                ->whereDate('created_at', now()->toDateString())
                ->selectRaw('COALESCE(currency, payment_gateway) as grouping_key, SUM(amount) as total_amount')
                ->groupBy('grouping_key')
                ->get()
                ->pluck('total_amount', 'grouping_key')
                ->toArray();

            // Подсчет суммы неудачных запросов за сегодня по валютам
            $sumByFailedCurrencyToday = MerchantApiRequestLog::query()
                ->where('is_successful', false)
                ->whereDate('created_at', now()->toDateString())
                ->selectRaw('COALESCE(currency, payment_gateway) as grouping_key, SUM(amount) as total_amount')
                ->groupBy('grouping_key')
                ->get()
                ->pluck('total_amount', 'grouping_key')
                ->toArray();

            // Подсчет суммы успешных запросов за все время по валютам
            $sumBySuccessCurrencyTotal = MerchantApiRequestLog::query()
                ->where('is_successful', true)
                ->selectRaw('COALESCE(currency, payment_gateway) as grouping_key, SUM(amount) as total_amount')
                ->groupBy('grouping_key')
                ->get()
                ->pluck('total_amount', 'grouping_key')
                ->toArray();

            // Подсчет суммы неудачных запросов за все время по валютам
            $sumByFailedCurrencyTotal = MerchantApiRequestLog::query()
                ->where('is_successful', false)
                ->selectRaw('COALESCE(currency, payment_gateway) as grouping_key, SUM(amount) as total_amount')
                ->groupBy('grouping_key')
                ->get()
                ->pluck('total_amount', 'grouping_key')
                ->toArray();

            $paymentGateways = PaymentGateway::query()
                ->select('id', 'code', 'currency')
                ->get()
                ->pluck('currency', 'code')
                ->toArray();

            $sumBySuccessCurrencyToday = $this->normalizeAllToCurrency($sumBySuccessCurrencyToday, $paymentGateways);
            $sumByFailedCurrencyToday = $this->normalizeAllToCurrency($sumByFailedCurrencyToday, $paymentGateways);
            $sumBySuccessCurrencyTotal = $this->normalizeAllToCurrency($sumBySuccessCurrencyTotal, $paymentGateways);
            $sumByFailedCurrencyTotal = $this->normalizeAllToCurrency($sumByFailedCurrencyTotal, $paymentGateways);

            return [
                'failedTotal' => $failedTotal,
                'failedToday' => $failedToday,
                'successTotal' => $successTotal,
                'successToday' => $successToday,
                'sumBySuccessCurrencyToday' => $sumBySuccessCurrencyToday,
                'sumByFailedCurrencyToday' => $sumByFailedCurrencyToday,
                'sumBySuccessCurrencyTotal' => $sumBySuccessCurrencyTotal,
                'sumByFailedCurrencyTotal' => $sumByFailedCurrencyTotal,
            ];
        });

        // Извлекаем переменные из кэша
        extract($statistics);

        return Inertia::render('MerchantApiLogs/Index', [
            'logs' => MerchantApiLogResource::collection($logs),
            'filters' => $filters,
            'filtersVariants' => $filtersVariants,
            'failedTotal' => $failedTotal,
            'failedToday' => $failedToday,
            'successTotal' => $successTotal,
            'successToday' => $successToday,
            'sumBySuccessCurrencyToday' => $sumBySuccessCurrencyToday,
            'sumByFailedCurrencyToday' => $sumByFailedCurrencyToday,
            'sumBySuccessCurrencyTotal' => $sumBySuccessCurrencyTotal,
            'sumByFailedCurrencyTotal' => $sumByFailedCurrencyTotal,
        ]);
    }

    protected function normalizeAllToCurrency($sums, array $paymentGateways): array
    {
        foreach ($sums as $key => $sum) {
            if (Currency::isCurrency($key)) {
                continue;
            }

            $currency = $paymentGateways[$key]->getCode();

            if (empty($sums[$currency])) {
                $sums[$currency] = 0;
            }

            $sums[$currency] += $sum;

            unset($sums[$key]);
        }

        return $sums;
    }

    protected function getApiLogFiltersData(): array
    {
        $statusVariants = [
            [
                'name' => 'Успешные',
                'value' => '1',
            ],
            [
                'name' => 'Неуспешные',
                'value' => '0',
            ],
        ];

        return [
            'statusVariants' => $statusVariants,
        ];
    }
}
