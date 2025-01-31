<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\SmsLogResource;
use App\Models\SenderStopList;
use App\Models\SmsLog;
use Inertia\Inertia;

class SmsLogController extends Controller
{
    public function index()
    {
        $currentFilters = [
            'search' => request()->input('filters.search'),
            'only_success_parsing' => request()->input('filters.only_success_parsing'),
        ];

        $query = SmsLog::query()
            ->with('user')
            ->when($currentFilters['search'], function ($query) use ($currentFilters) {
                $query->where('message', 'like', '%' . $currentFilters['search'] . '%');
            })
            ->when((bool)$currentFilters['only_success_parsing'], function ($query) use ($currentFilters) {
                $query->whereNotNull('parsing_result');
            });

        $sms_logs = $query->clone()
            ->orderByDesc('id')
            ->paginate(10);

        $sms_logs = SmsLogResource::collection($sms_logs);

        $smsLogsTotalCount = $query->clone()->count();

        $senderStopList = SenderStopList::all()
            ->transform(function ($item) {
                return [
                    'id' => $item->id,
                    'sender' => $item->sender,
                ];
            });

        return Inertia::render('SmsLog/Index', compact('sms_logs', 'smsLogsTotalCount', 'senderStopList', 'currentFilters'));
    }
}
