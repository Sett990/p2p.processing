<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\MerchantApiLogResource;
use App\Services\Statistics\MerchantApiStatisticsService;
use Inertia\Inertia;

class MerchantApiLogController extends Controller
{
    public function index(MerchantApiStatisticsService $statisticsService)
    {
        $filters = $this->getTableFilters();
        $filtersVariants = $this->getFiltersData();

        $logs = queries()->merchantApiLog()->paginateForAdmin($filters);
        
        // Получаем статистику из сервиса
        $statistics = $statisticsService->getStatistics();
        
        // Распаковываем переменные
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
}
