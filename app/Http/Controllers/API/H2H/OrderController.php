<?php

namespace App\Http\Controllers\API\H2H;

use App\Contracts\OrderServiceContract;
use App\DTO\Order\CreateOrderDTO;
use App\Enums\OrderStatus;
use App\Enums\OrderSubStatus;
use App\Exceptions\OrderException;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\H2H\Order\StoreRequest;
use App\Http\Resources\API\H2H\OrderResource;
use App\Models\Merchant;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Throwable;

class OrderController extends Controller
{
    public function show(Order $order): JsonResponse
    {
        if (! $order->is_h2h) {
            return response()->failWithMessage('Сделка предназначена не для H2H API, а для Merchant API.');
        }

        $order->load('dispute', 'paymentGateway', 'paymentDetail');

        Gate::authorize('access-to-order', $order);

        return response()->success(
            OrderResource::make($order)
        );
    }

    public function store(StoreRequest $request): JsonResponse
    {
        $merchant = queries()->merchant()->findByUUID($request->merchant_id);

        Gate::authorize('api-access-to-merchant', $merchant);

        services()->merchantApiLog()->logRequest($request, $merchant, $request->validated());

        try {
            $order = make(OrderServiceContract::class)->create(
                CreateOrderDTO::makeFromRequest($request->validated() + ['h2h' => true, 'merchant' => $merchant])
            );

            // Обновляем лог с успешным ответом
            $response = response()->success(
                OrderResource::make($order)
            );
            services()->merchantApiLog()->updateWithResponse($merchant, $request->external_id, $response, $order);

            return $response;
        } catch (OrderException $e) {
            // Обновляем лог с ошибкой OrderException
            $response = response()->failWithMessage($e->getMessage());
            services()->merchantApiLog()->updateWithResponse($merchant, $request->external_id, $response, null, $e);

            return $response;
        } catch (Throwable $e) {
            // Обновляем лог с ошибкой любого другого исключения
            $response = response()->failWithMessage('Произошла ошибка при обработке запроса');
            services()->merchantApiLog()->updateWithResponse($merchant, $request->external_id, $response, null, $e);

            throw $e;
        }
    }

    public function finish(Order $order): JsonResponse
    {
        if (is_production()) {
            abort(404);
        }

        if (! $order->is_h2h) {
            return response()->failWithMessage('Сделка предназначена не для H2H API, а для Merchant API.');
        }

        Gate::authorize('access-to-order', $order);

        if ($order->status->notEquals(OrderStatus::PENDING)) {
            return response()->failWithMessage('It is not possible to finish a completed order.');
        }
        if ($order->dispute) {
            return response()->failWithMessage('Unable to finish an order in dispute.');
        }

        try {
            services()->order()->finishOrderAsSuccessful($order->id, OrderSubStatus::CANCELED);

            $order->refresh();

            $order->load('dispute', 'paymentGateway', 'paymentDetail');

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
            services()->order()->finishOrderAsFailed($order->id, OrderSubStatus::CANCELED);

            $order->refresh();

            $order->load('dispute', 'paymentGateway', 'paymentDetail');

            return response()->success(
                OrderResource::make($order)
            );
        } catch (OrderException $e) {
            return response()->failWithMessage($e->getMessage());
        }
    }
}
