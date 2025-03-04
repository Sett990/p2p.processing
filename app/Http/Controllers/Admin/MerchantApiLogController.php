<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\MerchantApiLogResource;
use App\Models\MerchantApiRequestLog;
use Inertia\Inertia;

class MerchantApiLogController extends Controller
{
    public function index()
    {
        $logs = MerchantApiRequestLog::query()
            ->with(['merchant', 'order'])
            ->paginate(20);

        return Inertia::render('MerchantApiLogs/Index', [
            'logs' => MerchantApiLogResource::collection($logs),
        ]);
    }
}
