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
use App\Jobs\OrderPoolingJob;
use App\Models\Merchant;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
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

        $jobID = Str::uuid()->toString();
        $createdAt = now()->getTimestampMs();

        cache()->put("order:create:$jobID", json_encode([
            'status' => 'queued',
        ]), 60);

        OrderPoolingJob::dispatch($jobID, $createdAt, $request->validated());

        // Ожидание результата
        $maxWaitMs = 5000;
        $intervalMs = 500;
        $waited = 0;

        while ($waited < $maxWaitMs) {
            usleep($intervalMs * 1000);
            $waited += $intervalMs;

            if ($waited > $maxWaitMs) {
                break;
            }

            $result = cache()->get("order:create:$jobID");

            if ($result) {
                $data = json_decode($result, true);

                if ($data['status'] === 'done') {
                    /**
                     * @var Order $order
                     */
                    $order = Order::find($data['order_id']);

                    // Обновляем лог с успешным ответом
                    $response = response()->success(
                        OrderResource::make($order)
                    );
                    services()->merchantApiLog()->updateWithResponse($merchant, $request->external_id, $response, $order);

                    return $response;
                } elseif ($data['status'] === 'failed') {
                    if ($data['exception'] instanceof OrderException) {
                        $response = response()->failWithMessage($data['exception']->getMessage());
                        services()->merchantApiLog()->updateWithResponse($merchant, $request->external_id, $response, null, $data['exception']);

                        return $response;
                    } elseif ($data['exception'] instanceof Throwable) {
                        $response = response()->failWithMessage('Произошла ошибка при обработке запроса');
                        services()->merchantApiLog()->updateWithResponse($merchant, $request->external_id, $response, null, $data['exception']);

                        return $response;
                    } else {
                        $response = response()->failWithMessage('Произошла неизвестная ошибка при обработке запроса');
                        services()->merchantApiLog()->updateWithResponse($merchant, $request->external_id, $response);

                        return $response;
                    }
                } elseif ($data['status'] === 'expired') {
                    break;
                }
            }
        }

        $response = response()->failWithMessage('Не удалось обработать запрос вовремя. Повторите попытку позже.', 504);
        services()->merchantApiLog()->updateWithResponse($merchant, $request->external_id, $response);

        return $response;

        dd(1);

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

            return $response;
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
