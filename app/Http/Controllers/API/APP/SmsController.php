<?php

namespace App\Http\Controllers\API\APP;

use App\DTO\SMS\SmsDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\SMS\StoreRequest;
use App\Jobs\HandleSmsJob;
use App\Models\SenderStopList;
use App\Models\UserDevice;
use App\Services\Sms\Utils\NormalizeMessage;

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
                return UserDevice::query()
                    ->where('token', $request->header('Access-Token'))
                    ->first([
                        'id',
                        'android_id',
                        'user_id',
                    ]);
            }
        );

        if (!$device) {
            return response()->failWithMessage('Неверный токен устройства', 401);
        }

        if (!$device->android_id) {
            return response()->failWithMessage('Устройство не подключено', 401);
        }

        cache()->put("user-apk-latest-ping-at-$device->user_id", now()->toDateTimeString());

        $sender = NormalizeMessage::normalize($request->sender);

        // Получаем список отправителей из кеша или базы данных
        $senderStopList = cache()->remember('sender_stop_list', now()->addMinutes(10), function () {
            return SenderStopList::query()->get('sender')->pluck('sender')->toArray();
        });

        if (in_array($sender, $senderStopList)) {
            return response()->success();
        }

        HandleSmsJob::dispatchSync(
            SmsDTO::fromArray($request->validated() + [
                    'deviceID' => $device->id,
                ])
        );

        return response()->success();
    }
}
