<?php

namespace App\Services\Order\Features\OrderDetailProvider\Filters;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\PaymentDetail;
use App\Services\Order\Features\OrderDetailProvider\Values\Detail;
use Illuminate\Support\Collection as SupportCollection;

class UniqueAmount extends BaseFilter
{
    protected SupportCollection $busyAmountsByPaymentDetail;

    public function __construct()
    {
        // Оптимизированный запрос: получаем только необходимые данные для проверки уникальности сумм
        // Сначала получаем ID платежных деталей для ожидающих заказов
        $pendingOrders = Order::query()
            ->where('status', OrderStatus::PENDING)
            ->select('id', 'payment_detail_id', 'amount', 'currency', 'payment_gateway_id')
            ->get();

        // Получаем платежные детали с их ID и связанными данными
        $paymentDetails = PaymentDetail::query()
            ->whereIn('id', $pendingOrders->pluck('payment_detail_id')->unique())
            ->select(['id', 'user_device_id', 'user_id'])
            ->with('paymentGateways:id')
            ->get();

        // Создаем структуру данных для быстрой проверки уникальности сумм
        $this->busyAmountsByPaymentDetail = $paymentDetails->map(function ($paymentDetail) use ($pendingOrders) {
            $detailOrders = $pendingOrders->where('payment_detail_id', $paymentDetail->id);

            return [
                'payment_detail' => $paymentDetail,
                'amounts' => $detailOrders->map(function ($order) {
                    return [
                        'amount' => intval($order->amount->toUnits()),
                        'gateway_id' => $order->payment_gateway_id
                    ];
                })->toArray()
            ];
        })->collect();
    }

    public function check(Detail $detail): bool
    {
        $amount = (int)$detail->amount->toUnits();

        $isUniqueAmount = $this->isUniqueAmount(
            $detail->gateway->id,
            $detail->userDeviceID,
            $detail->trader->id,
            $amount
        );

        if (!$isUniqueAmount) {
            return false;
        }

        return true;
    }

    protected function isUniqueAmount(int $gatewayId, int $userDeviceId, int $userId, int $amount): bool
    {
        $matchingDetails = $this->busyAmountsByPaymentDetail
            ->filter(function ($item) use ($userDeviceId, $userId) {
                $paymentDetail = $item['payment_detail'];
                return $paymentDetail->user_device_id === $userDeviceId
                    && $paymentDetail->user_id === $userId;
            });

        foreach ($matchingDetails as $item) {
            foreach ($item['amounts'] as $amountData) {
                if ($amountData['gateway_id'] === $gatewayId && $amountData['amount'] === $amount) {
                    return false;
                }
            }
        }

        return true;
    }
}
