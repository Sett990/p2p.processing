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
        $sms_logs = SmsLog::query()
            ->with('user')
            ->orderByDesc('id')
            ->paginate(10);

        $sms_logs = SmsLogResource::collection($sms_logs);

        $smsLogsTotalCount = SmsLog::query()->count();

        $senderStopList = SenderStopList::all()
            ->transform(function ($item) {
                return [
                    'id' => $item->id,
                    'sender' => $item->sender,
                ];
            });

        return Inertia::render('SmsLog/Index', compact('sms_logs', 'smsLogsTotalCount', 'senderStopList'));
    }
}
