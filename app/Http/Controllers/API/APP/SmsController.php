<?php

namespace App\Http\Controllers\API\APP;

use App\DTO\SMS\SmsDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\SMS\StoreRequest;
use App\Jobs\HandleSmsJob;
use App\Models\UserDevice;

class SmsController extends Controller
{
    public function store(StoreRequest $request)
    {
        $device = UserDevice::where('token', $request->header('Access-Token'))->first();

        if (!$device) {
            return response()->failWithMessage('Неверный токен устройства', 401);
        }

        if (!$device->android_id) {
            return response()->failWithMessage('Устройство не подключено', 401);
        }

        $user = $device->user;

        cache()->put("user-apk-latest-ping-at-$user->id", now()->toDateTimeString());

        HandleSmsJob::dispatch(
            SmsDTO::fromArray($request->validated() + [
                    'user' => $user,
                    'device' => $device,
                ])
        );

        return response()->success();
    }
}
