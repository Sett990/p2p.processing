<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\API\H2H\OrderResource;
use App\Http\Resources\UserResource;
use App\Models\Order;
use App\Models\PaymentDetail;
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
}
