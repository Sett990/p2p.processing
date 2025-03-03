<?php

namespace App\Http\Controllers\API\APP;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\UserDeviceResource;
use App\Models\UserDevice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DeviceController extends Controller
{
    /**
     * Подключает устройство к токену
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function connect(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'android_id' => 'required|string|unique:user_devices,android_id',
            'device_model' => 'required|string',
            'android_version' => 'required|string',
            'manufacturer' => 'required|string',
            'brand' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Ошибка валидации',
                'errors' => $validator->errors()
            ], 422);
        }

        $device = UserDevice::where('token', $request->header('Access-Token'))->first();

        if (!$device) {
            return response()->json([
                'message' => 'Неверный токен устройства'
            ], 401);
        }

        if ($device->android_id) {
            return response()->json([
                'message' => 'Этот токен уже привязан к другому устройству'
            ], 400);
        }

        $device->update([
            'android_id' => $request->android_id,
            'device_model' => $request->device_model,
            'android_version' => $request->android_version,
            'manufacturer' => $request->manufacturer,
            'brand' => $request->brand,
            'connected_at' => now(),
        ]);

        return response()->success(new UserDeviceResource($device));
    }

    /**
     * Получает информацию об устройстве
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function info(Request $request)
    {
        $device = UserDevice::where('token', $request->header('Access-Token'))->first();

        if (!$device) {
            return response()->json([
                'message' => 'Неверный токен устройства'
            ], 401);
        }

        return response()->success(new UserDeviceResource($device));
    }
}
