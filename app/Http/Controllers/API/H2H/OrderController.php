<?php

namespace App\Http\Controllers\API\H2H;

use App\Contracts\OrderServiceContract;
use App\DTO\Order\CreateOrderDTO;
use App\Enums\OrderStatus;
use App\Exceptions\OrderException;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\H2H\Order\StoreRequest;
use App\Http\Resources\API\H2H\OrderResource;
use App\Models\Merchant;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class OrderController extends Controller
{
    public function show(Order $order): JsonResponse
    {
        if (! $order->is_h2h) {
            return response()->failWithMessage('Сделка предназначена не для H2H API, а для Merchant API.');
        }

        Gate::authorize('access-to-order', $order);

        return response()->success(
            OrderResource::make($order)
        );
    }

    public function store(StoreRequest $request): JsonResponse
    {
        $merchant = Merchant::where('uuid', $request->merchant_id)->first();

        Gate::authorize('access-to-merchant', $merchant);

        try {
            $order = make(OrderServiceContract::class)->create(
                CreateOrderDTO::makeFromRequest($request->validated() + ['h2h' => true])
            );

            return response()->success(
                OrderResource::make($order)
            );
        } catch (OrderException $e) {
            return response()->failWithMessage($e->getMessage());
        }
    }

    public function cancel(Order $order): JsonResponse
    {
        if (! $order->is_h2h) {
            return response()->failWithMessage('Сделка предназначена не для H2H API, а для Merchant API.');
        }

        Gate::authorize('access-to-order', $order);

        if ($order->status->notEquals(OrderStatus::PENDING)) {
            return response()->failWithMessage('It is not possible to cancel a completed order.');
        }
        if ($order->dispute) {
            return response()->failWithMessage('Unable to cancel an order in dispute.');
        }

        try {
            services()->order()->finishOrderAsFailed($order);

            return response()->success(
                OrderResource::make($order)
            );
        } catch (OrderException $e) {
            return response()->failWithMessage($e->getMessage());
        }
    }
}
