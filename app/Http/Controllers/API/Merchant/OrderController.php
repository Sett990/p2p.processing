<?php

namespace App\Http\Controllers\API\Merchant;

use App\Contracts\OrderServiceContract;
use App\DTO\Order\CreateOrderDTO;
use App\Exceptions\OrderException;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\Merchant\Order\StoreRequest;
use App\Http\Resources\API\Merchant\OrderResource;
use App\Models\Merchant;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Throwable;

class OrderController extends Controller
{
    public function show(Order $order): JsonResponse
    {
        if ($order->is_h2h) {
            return response()->failWithMessage('Сделка предназначена для H2H API.');
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

        // Логируем запрос
        $log = services()->merchantApiLog()->logRequest($request, $merchant, $request->validated());

        try {
            $order = make(OrderServiceContract::class)->create(
                CreateOrderDTO::makeFromRequest($request->validated() + ['merchant' => $merchant])
            );

            // Обновляем лог с успешным ответом
            $response = response()->success(OrderResource::make($order));
            services()->merchantApiLog()->updateWithResponse($log, $response, $order);

            return $response;
        } catch (OrderException $e) {
            // Обновляем лог с ошибкой OrderException
            $response = response()->failWithMessage($e->getMessage());
            services()->merchantApiLog()->updateWithResponse($log, $response, null, $e);

            return $response;
        } catch (Throwable $e) {
            // Обновляем лог с ошибкой любого другого исключения
            $response = response()->failWithMessage('Произошла ошибка при обработке запроса');
            services()->merchantApiLog()->updateWithResponse($log, $response, null, $e);

            throw $e;
        }
    }
}
