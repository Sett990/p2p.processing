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

        OrderPoolingJob::dispatchSync($jobID, $createdAt, $request->validated());

        // Ожидание результата
        $maxWaitMs = config('order-pooling.max_wait_time');
        $intervalMs = config('order-pooling.poll_interval');
        $waited = 0;
        $processingTimeMs = 0;
        $maxWaitProcessingMs = 3000;

        while ($waited < $maxWaitMs) {
            usleep($intervalMs * 1000);
            $waited += $intervalMs;

            $result = cache()->get("order:create:$jobID");

            if ($result) {
                $data = json_decode($result, true);

                if (empty($data['status'])) {
                    break;
                }

                if ($data['status'] === 'queued' && $waited > $maxWaitMs + $intervalMs) {
                    break;
                }

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
                    if (empty($data['exception']['class']) || empty($data['exception']['message'])) {
                        $response = response()->failWithMessage('Произошла неизвестная ошибка при обработке запроса');
                        services()->merchantApiLog()->updateWithResponse($merchant, $request->external_id, $response);

                        return $response;
                    }

                    if (is_a($data['exception']['class'], OrderException::class, true)) {
                        $response = response()->failWithMessage($data['exception']['message']);
                        services()->merchantApiLog()->updateWithResponse($merchant, $request->external_id, $response, null, $data['exception']['class'], $data['exception']['message']);

                        return $response;
                    } elseif (is_a($data['exception']['class'], Throwable::class, true)) {
                        $response = response()->failWithMessage('Произошла ошибка при обработке запроса');
                        services()->merchantApiLog()->updateWithResponse($merchant, $request->external_id, $response, null, $data['exception']['class'], $data['exception']['message']);

                        return $response;
                    } else {
                        $response = response()->failWithMessage('Произошла неизвестная ошибка при обработке запроса');
                        services()->merchantApiLog()->updateWithResponse($merchant, $request->external_id, $response);

                        return $response;
                    }
                } elseif ($data['status'] === 'expired') {
                    break;
                } elseif ($data['status'] === 'processing') {
                    $processingTimeMs = $processingTimeMs + $intervalMs;

                    if ($processingTimeMs > $maxWaitProcessingMs) {
                        break;
                    }
                }
            } else {
                break;
            }
        }

        $response = response()->failWithMessage('Не удалось обработать запрос вовремя. Повторите попытку позже.', 504);
        services()->merchantApiLog()->updateWithResponse($merchant, $request->external_id, $response);

        return $response;
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
