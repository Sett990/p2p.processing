<?php

namespace App\Http\Controllers\API\Bot;

use App\Http\Controllers\Controller;
use App\Http\Resources\API\H2H\OrderResource;
use App\Http\Resources\UserResource;
use App\Models\Order;
use Illuminate\Http\Request;

class BotController extends Controller
{
    public function index(Order $order)
    {
        return response()->success([
            'order' => OrderResource::make($order)->resolve(),
            'detail' => UserResource::make($order->paymentDetail)->resolve(),
            'user' => UserResource::make($order->paymentDetail->user)->resolve(),
        ]);
    }

    public function acceptDispute(Order $order)
    {
        if (! $order->dispute) {
            return response()->failWithMessage('Dispute not found.');
        }

        services()->dispute()->accept($order->dispute);

        return response()->success();
    }

    public function cancelDispute(Request $request, Order $order)
    {
        $request->validate([
            'reason' => ['required', 'string', 'max:255'],
        ]);

        if (! $order->dispute) {
            return response()->failWithMessage('Dispute not found.');
        }

        services()->dispute()->cancel($order->dispute, $request->reason);

        return response()->success();
    }
}
