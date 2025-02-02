<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\TransactionType;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Carbon\Carbon;
use Inertia\Inertia;
use Illuminate\Support\Facades\Gate;

class OrderController extends Controller
{
    public function index()
    {
        $statuses = request()->input('filters.statuses', '');
        $statuses = explode(',', $statuses);

        foreach ($statuses as $key => $value) {
            if (! OrderStatus::tryFrom($value)) {
                unset($statuses[$key]);
            }
        }

        $startDate = request()->input('filters.start_date');
        if ($startDate) {
            $startDate = Carbon::createFromFormat('d/m/Y', $startDate);
        }

        $endDate = request()->input('filters.end_date');
        if ($endDate) {
            $endDate = Carbon::createFromFormat('d/m/Y', $endDate);
        }

        if ($startDate && $endDate?->lessThan($startDate)) {
            $endDate = null;
        }

        $uuid = request()->input('filters.uuid');

        $orders = queries()->order()->paginateForUser(auth()->user(), $statuses, $startDate, $endDate, $uuid);

        $orders = OrderResource::collection($orders);

        $orderStatuses = [];
        foreach (OrderStatus::values() as $status) {
            $orderStatuses[] = [
                'name' => trans("order.status.{$status}"),
                'value' => $status,
            ];
        }

        $currentFilters = [
            'statuses' => $statuses,
            'startDate' => $startDate?->format('d/m/Y'),
            'endDate' => $endDate?->format('d/m/Y'),
            'uuid' => $uuid,
        ];

        return Inertia::render('Order/Index', compact('orders', 'orderStatuses', 'currentFilters'));
    }

    public function acceptOrder(Order $order)
    {
        Gate::authorize('access-to-order', $order);

        if ($order->status->equals(OrderStatus::SUCCESS)) {
            return;
        }

        if ($order->status->equals(OrderStatus::FAIL)) {
            services()->order()->rollback($order, TransactionType::PAYMENT_FOR_OPENED_ORDER);
        }

        services()->order()->succeed($order);
    }
}
