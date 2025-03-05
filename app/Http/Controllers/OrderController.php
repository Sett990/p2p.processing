<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\OrderSubStatus;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Carbon\Carbon;
use Inertia\Inertia;
use Illuminate\Support\Facades\Gate;

class OrderController extends Controller
{
    public function index()
    {
        $filters = $this->getTableFilters();
        $filtersVariants = $this->getFiltersData();

        $orders = queries()->order()->paginateForUser(auth()->user(), $filters);
        $orders = OrderResource::collection($orders);

        return Inertia::render('Order/Index', compact('orders', 'filters', 'filtersVariants'));
    }

    public function acceptOrder(Order $order)
    {
        Gate::authorize('access-to-order', $order);

        if ($order->dispute) {
            return;
        }

        if ($order->status->equals(OrderStatus::SUCCESS)) {
            return;
        }

        if ($order->status->equals(OrderStatus::FAIL)) {
            services()->order()->reopenFinishedOrder($order->id, OrderSubStatus::WAITING_FOR_PAYMENT);
        }

        services()->order()->finishOrderAsSuccessful($order->id, OrderSubStatus::ACCEPTED);
    }
}
