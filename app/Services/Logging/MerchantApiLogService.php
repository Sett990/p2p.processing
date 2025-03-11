<?php

namespace App\Services\Logging;

use App\Contracts\MerchantApiLogServiceContract;
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
     * @return MerchantApiRequestLog Созданный лог
     */
    public function logRequest(Request $request, Merchant $merchant, array $requestData): MerchantApiRequestLog
    {
        return MerchantApiRequestLog::create([
            'external_id' => $requestData['external_id'] ?? null,
            'amount' => $requestData['amount'] ?? null,
            'currency' => $requestData['currency'] ?? null,
            'payment_gateway' => $requestData['payment_gateway'] ?? null,
            'payment_detail_type' => $requestData['payment_detail_type'] ?? null,
            'request_data' => $requestData,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'is_successful' => false, // По умолчанию считаем неуспешным, обновим после ответа
            'merchant_id' => $merchant->id,
        ]);
    }

    /**
     * Обновляет лог после получения ответа
     *
     * @param MerchantApiRequestLog $log Объект лога
     * @param JsonResponse $response Объект ответа
     * @param Order|null $order Созданный заказ (если успешно)
     * @param Throwable|null $exception Исключение, если оно возникло
     * @return MerchantApiRequestLog Обновленный лог
     */
    public function updateWithResponse(MerchantApiRequestLog $log, JsonResponse $response, ?Order $order = null, ?Throwable $exception = null): MerchantApiRequestLog
    {
        $responseData = json_decode($response->getContent(), true);
        $isSuccessful = $response->getStatusCode() === 200 && ($responseData['success'] ?? '') === true;

        $updateData = [
            'order_id' => $order?->id,
            'response_data' => $responseData,
            'is_successful' => $isSuccessful,
            'error_message' => $isSuccessful ? null : ($responseData['message'] ?? 'Неизвестная ошибка'),
        ];

        // Если есть исключение и оно не является OrderException, записываем информацию о нем
        if ($exception !== null && !str_contains(get_class($exception), 'OrderException')) {
            $updateData['exception_class'] = get_class($exception);
            $updateData['exception_message'] = $exception->getMessage();
        }

        $log->update($updateData);

        return $log;
    }
}
