<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\MerchantApiLogResource;
use App\Models\MerchantApiRequestLog;
use App\Models\Merchant;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MerchantApiLogController extends Controller
{
    /**
     * Отображает список логов API-запросов от мерчантов.
     *
     * @param Request $request
     * @return \Inertia\Response
     */
    public function index(Request $request)
    {
        $request->validate([
            'search' => 'nullable|string',
            'merchant_id' => 'nullable|exists:merchants,id',
            'is_successful' => 'nullable|boolean',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
            'per_page' => 'nullable|integer|min:10|max:100',
            'sort_by' => 'nullable|string|in:id,created_at,external_id,amount,is_successful',
            'sort_direction' => 'nullable|string|in:asc,desc',
        ]);

        $query = MerchantApiRequestLog::query()
            ->with(['merchant', 'order']);

        // Фильтрация по поисковому запросу
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('external_id', 'like', "%{$search}%")
                  ->orWhere('error_message', 'like', "%{$search}%")
                  ->orWhereHas('merchant', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Фильтрация по мерчанту
        if ($request->filled('merchant_id')) {
            $query->where('merchant_id', $request->input('merchant_id'));
        }

        // Фильтрация по успешности
        if ($request->filled('is_successful')) {
            $query->where('is_successful', $request->boolean('is_successful'));
        }

        // Фильтрация по дате
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        // Сортировка
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');
        $query->orderBy($sortBy, $sortDirection);

        // Пагинация
        $perPage = $request->input('per_page', 15);
        $logs = $query->paginate($perPage)->withQueryString();

        // Получаем список мерчантов для фильтра
        $merchants = Merchant::select('id', 'name')->get();

        return Inertia::render('MerchantApiLogs/Index', [
            'logs' => MerchantApiLogResource::collection($logs),
            'merchants' => $merchants,
            'filters' => [
                'search' => $request->input('search', ''),
                'merchant_id' => $request->input('merchant_id'),
                'is_successful' => $request->input('is_successful'),
                'date_from' => $request->input('date_from'),
                'date_to' => $request->input('date_to'),
                'per_page' => $perPage,
                'sort_by' => $sortBy,
                'sort_direction' => $sortDirection,
            ],
        ]);
    }

    /**
     * Отображает детали конкретного лога API-запроса.
     *
     * @param MerchantApiRequestLog $log
     * @return \Inertia\Response
     */
    public function show(MerchantApiRequestLog $log)
    {
        $log->load(['merchant', 'order']);

        return Inertia::render('MerchantApiLogs/Show', [
            'log' => new MerchantApiLogResource($log),
        ]);
    }
}
