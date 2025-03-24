<?php

namespace App\Services\Logging;

use App\Contracts\MerchantApiLogServiceContract;
use App\Exceptions\OrderException;
use App\Jobs\CreateMerchantApiLogJob;
use App\Jobs\UpdateMerchantApiLogJob;
use App\Models\Merchant;
use App\Models\MerchantApiRequestLog;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Throwable;

class MerchantApiLogService implements MerchantApiLogServiceContract
{
    /**
     * Логирует запрос от мерчанта на создание сделки
     *
     * @param Request $request Объект запроса
     * @param Merchant $merchant Объект мерчанта
     * @param array $requestData Данные запроса
     */
    public function logRequest(Request $request, Merchant $merchant, array $requestData): void
    {
        CreateMerchantApiLogJob::dispatch(
            $merchant,
            $requestData,
            $request->ip(),
            $request->userAgent()
        );
    }

    /**
     * Обновляет лог после получения ответа
     *
     * @param Merchant $merchant
     * @param string $externalID
     * @param JsonResponse $response Объект ответа
     * @param Order|null $order Созданный заказ (если успешно)
     * @param Throwable|null $exception Исключение, если оно возникло
     */
    public function updateWithResponse(Merchant $merchant, string $externalID, JsonResponse $response, ?Order $order = null, ?string $exceptionClass = null, ?string $exceptionMessage = null): void
    {
        $responseData = json_decode($response->getContent(), true);
        $isSuccessful = $response->getStatusCode() === 200 && ($responseData['success'] ?? '') === true;

        $errorMessage = $isSuccessful ? null : ($responseData['message'] ?? 'Неизвестная ошибка');

        // Если есть исключение и оно не является OrderException, записываем информацию о нем
        if (is_a($exceptionClass, OrderException::class, true)) {
            $exceptionClass = null;
            $exceptionMessage = null;
        }

        UpdateMerchantApiLogJob::dispatch(
            $merchant->id,
            $externalID,
            $responseData,
            $isSuccessful,
            $errorMessage,
            $order?->id,
            $exceptionClass,
            $exceptionMessage
        );
    }
}
