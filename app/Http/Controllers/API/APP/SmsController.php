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
        /**
         * @var UserDevice $device
         */
        $device = cache()->remember(
            'device_by_token_' . $request->header('Access-Token'),
            now()->addMinutes(10),
            function () use ($request) {
                return UserDevice::where('token', $request->header('Access-Token'))->first();
            }
        );

        if (!$device) {
            return response()->failWithMessage('Неверный токен устройства', 401);
        }

        if (!$device->android_id) {
            return response()->failWithMessage('Устройство не подключено', 401);
        }

        cache()->put("user-apk-latest-ping-at-$device->user_id", now()->toDateTimeString());

        HandleSmsJob::dispatchSync(
            SmsDTO::fromArray($request->validated() + [
                    'deviceID' => $device->id,
                ])
        );

        return response()->success();
    }
}
