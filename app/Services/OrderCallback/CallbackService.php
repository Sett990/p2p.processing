<?php

namespace App\Services\OrderCallback;

use App\Contracts\OrderCallbackServiceContract;
use App\Models\Order;
use Illuminate\Support\Facades\Http;

class OrderCallbackService implements OrderCallbackServiceContract
{
    public function send(Order $order): void
    {
        $order->load(['paymentDetail.subPaymentGateway', 'paymentGateway', 'smsLog', 'merchant', 'dispute']);

        $callback_url = $order->callback_url ?? $order->merchant->callback_url;

        if (! $callback_url) {
            return;
        }

        if ($order->is_h2h) {
            $data = \App\Http\Resources\API\H2H\OrderResource::make($order)->resolve();
        } else {
            $data = \App\Http\Resources\API\Merchant\OrderResource::make($order)->resolve();
        }

        $token = $order->merchant->user->api_access_token;

        Http::withoutVerifying()
            ->withHeader('Access-Token', $token)
            ->post(
                url: $callback_url,
                data: $data
            );
    }
}
