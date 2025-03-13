<?php

namespace App\Services\Order\Utils;

use App\Models\Order;
use Illuminate\Support\Facades\Redis;

class LatestFinishedOrders
{
    public static function store(Order $order): void
    {
        $redisKey = "latest-finished-orders:{$order->id}";

        $time = $order->expires_at->clone()->addMinutes(10);
        $time = (int)now()->diffInSeconds($time);

        Redis::setex($redisKey, $time, json_encode([
            'amount' => $order->amount->toUnits(),
            'payment_gateway_id' => $order->payment_gateway_id,
            'user_device_id' => $order->paymentDetail->user_device_id,
        ]));
    }

    public static function get(): array
    {
        //$keys = Redis::keys("latest-finished-orders:order-*:payment_gateway-{$paymentGatewayID}:user_device-{$userDeviceID}");
        $keys = Redis::keys("latest-finished-orders:*");

        $orderData = [];
        foreach ($keys as $key) {
            $prefix = config('database.redis.options.prefix', '');
            $key = str_replace($prefix, '', $key);

            $orderData[] = json_decode(Redis::get($key), true);
        }

        return $orderData;
    }
}
