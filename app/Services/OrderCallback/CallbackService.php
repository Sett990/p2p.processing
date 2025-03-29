<?php

namespace App\Services\OrderCallback;

use App\Contracts\CallbackServiceContract;
use App\Models\Order;
use App\Models\Payout;
use Illuminate\Support\Facades\Http;

class CallbackService implements CallbackServiceContract
{
    public function sendForOrder(Order $order): void
    {
        $order->load(['paymentDetail', 'paymentGateway', 'smsLog', 'merchant', 'dispute']);

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

    public function sendForPayout(Payout $payout): void
    {
        $callback_url = $payout->callback_url ?? $payout->payoutGateway->callback_url;

        if (! $callback_url) {
            return;
        }

        $data = \App\Http\Resources\API\PayoutResource::make($payout)->resolve();

        $token = $payout->owner->api_access_token;

        Http::withoutVerifying()
            ->withHeader('Access-Token', $token)
            ->post(
                url: $callback_url,
                data: $data
            );
    }
}
