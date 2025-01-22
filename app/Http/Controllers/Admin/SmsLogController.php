<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\SmsLogResource;
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

        return Inertia::render('SmsLog/Index', compact('sms_logs', 'smsLogsTotalCount'));
    }
}
