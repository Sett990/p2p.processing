<?php

namespace App\Contracts;

use App\Models\Merchant;
use App\Models\MerchantApiRequestLog;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

interface MerchantApiLogServiceContract
{
    /**
     * Логирует запрос от мерчанта на создание сделки
     *
     * @param Request $request Объект запроса
     * @param Merchant $merchant Объект мерчанта
     * @param array $requestData Данные запроса
     * @return MerchantApiRequestLog Созданный лог
     */
    public function logRequest(Request $request, Merchant $merchant, array $requestData): MerchantApiRequestLog;

    /**
     * Обновляет лог после получения ответа
     *
     * @param MerchantApiRequestLog $log Объект лога
     * @param JsonResponse $response Объект ответа
     * @param Order|null $order Созданный заказ (если успешно)
     * @return MerchantApiRequestLog Обновленный лог
     */
    public function updateWithResponse(MerchantApiRequestLog $log, JsonResponse $response, ?Order $order = null): MerchantApiRequestLog;
} 