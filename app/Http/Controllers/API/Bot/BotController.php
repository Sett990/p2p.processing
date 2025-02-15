<?php

namespace App\Http\Controllers\API\Bot;

use App\Http\Controllers\Controller;
use App\Http\Resources\API\H2H\OrderResource;
use App\Http\Resources\UserResource;
use App\Models\Order;

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
}
