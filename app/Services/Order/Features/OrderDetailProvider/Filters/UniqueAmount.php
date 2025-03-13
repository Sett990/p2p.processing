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
            ->select('id', 'payment_detail_id', 'amount', 'currency')
            ->get();

        // Получаем платежные детали с их ID и связанными данными
        $paymentDetails = PaymentDetail::query()
            ->whereIn('id', $pendingOrders->pluck('payment_detail_id')->unique())
            ->select(['id', 'payment_gateway_id', 'user_device_id', 'user_id', 'sub_payment_gateway_id'])
            ->get();

        // Создаем структуру данных для быстрой проверки уникальности сумм
        $this->busyAmountsByPaymentDetail = $paymentDetails->map(function ($paymentDetail) use ($pendingOrders) {
            $detailOrders = $pendingOrders->where('payment_detail_id', $paymentDetail->id);

            return [
                'payment_detail' => $paymentDetail,
                'amounts' => $detailOrders->map(function ($order) {
                    return intval($order->amount->toUnits());
                })->toArray()
            ];
        })->collect();
    }

    public function check(Detail $detail): bool
    {
        $amount = (int)$detail->amount->toUnits();

        // Проверка уникальности суммы для обычного платежного шлюза
        $isUniqueAmount = $this->isUniqueAmount(
            $detail->gateway->id,
            $detail->userDeviceID,
            $detail->trader->id,
            $amount
        );

        if (!$isUniqueAmount) {
            return false;
        }

        // Проверка уникальности для СБП
        return $this->isUniqueSBPAmount($detail, $amount);
    }

    protected function isUniqueAmount(int $gatewayId, int $userDeviceId, int $userId, int $amount): bool
    {
        $matchingDetails = $this->busyAmountsByPaymentDetail
            ->filter(function ($item) use ($gatewayId, $userDeviceId, $userId) {
                $paymentDetail = $item['payment_detail'];
                return $paymentDetail->payment_gateway_id === $gatewayId
                    && $paymentDetail->user_device_id === $userDeviceId
                    && $paymentDetail->user_id === $userId;
            });

        foreach ($matchingDetails as $item) {
            if (in_array($amount, $item['amounts'])) {
                return false;
            }
        }

        return true;
    }

    protected function isUniqueSBPAmount(Detail $detail, int $amount): bool
    {
        if ($detail->gateway->isSBP) {
            // Если метод СБП, проверяем уникальность для подметода
            return $this->isUniqueAmount(
                $detail->subPaymentGatewayID,
                $detail->userDeviceID,
                $detail->trader->id,
                $amount
            );
        } else {
            // Если метод не СБП, проверяем уникальность для СБП с таким подметодом
            $matchingDetails = $this->busyAmountsByPaymentDetail
                ->filter(function ($item) use ($detail) {
                    $paymentDetail = $item['payment_detail'];
                    return $paymentDetail->sub_payment_gateway_id === $detail->paymentGatewayID
                        && $paymentDetail->user_device_id === $detail->userDeviceID
                        && $paymentDetail->user_id === $detail->trader->id;
                });

            foreach ($matchingDetails as $item) {
                if (in_array($amount, $item['amounts'])) {
                    return false;
                }
            }

            return true;
        }
    }
}
