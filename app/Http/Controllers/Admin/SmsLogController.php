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
        $filters = $this->getTableFilters();
        $filtersVariants = $this->getFiltersData();

        $query = SmsLog::query()
            ->with('user')
            ->when($filters->search, function ($query) use ($filters) {
                $query->where('message', 'like', '%' . strtolower($filters->search) . '%');
            })
            ->when($filters->onlySuccessParsing, function ($query) {
                $query->whereNotNull('parsing_result');
            });

        $smsLogs = $query->clone()
            ->orderByDesc('id')
            ->paginate(10);

        $smsLogs = SmsLogResource::collection($smsLogs);

        $smsLogsTotalCount = $query->clone()->count();

        $senderStopList = SenderStopList::all()
            ->transform(function ($item) {
                return [
                    'id' => $item->id,
                    'sender' => $item->sender,
                ];
            });

        return Inertia::render('SmsLog/Index', compact('smsLogs', 'smsLogsTotalCount', 'senderStopList', 'filters', 'filtersVariants'));
    }
}
