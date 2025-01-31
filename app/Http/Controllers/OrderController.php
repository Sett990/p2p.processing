<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Inertia\Inertia;
use Illuminate\Support\Facades\Gate;

class OrderController extends Controller
{
    public function index()
    {
        $orders = queries()->order()->paginateForUser(auth()->user());

        $orders = OrderResource::collection($orders);

        return Inertia::render('Order/Index', compact('orders'));
    }

    public function acceptOrder(Order $order)
    {
        Gate::authorize('access-to-order', $order);

        if ($order->status->equals(OrderStatus::SUCCESS)) {
            return;
        }

        if ($order->status->equals(OrderStatus::FAIL)) {
            services()->order()->reopenFinishedOrder($order);
        }

        services()->order()->finishOrderAsSuccessful($order);
    }
}
