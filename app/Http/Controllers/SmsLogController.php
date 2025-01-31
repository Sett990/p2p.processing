<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\SmsLogResource;
use App\Models\SmsLog;
use Inertia\Inertia;

class SmsLogController extends Controller
{
    public function index()
    {
        $currentFilters = [
            'search' => request()->input('filters.search'),
        ];

        $sms_logs = SmsLog::query()
            ->whereRelation('user', 'id', auth()->id())
            ->whereNotNull('order_id')
            ->when($currentFilters['search'], function ($query) use ($currentFilters) {
                $query->where('message', 'like', '%' . $currentFilters['search'] . '%');
            })
            ->orderByDesc('id')
            ->paginate(10);

        $sms_logs = SmsLogResource::collection($sms_logs);

        return Inertia::render('SmsLog/Index', compact('sms_logs', 'currentFilters'));
    }
}
