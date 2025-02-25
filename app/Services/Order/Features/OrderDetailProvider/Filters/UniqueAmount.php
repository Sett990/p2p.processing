<?php

namespace App\Services\Order\Features\OrderDetailProvider\Filters;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\PaymentDetail;
use App\Services\Order\Features\OrderDetailProvider\Values\Detail;
use Illuminate\Database\Eloquent\Collection;

class UniqueAmount extends BaseFilter
{
    protected Collection $busyPaymentDetails;

    public function __construct()
    {
        $pendingOrdersIDs = Order::query()
            ->where('status', OrderStatus::PENDING)
            ->get('payment_detail_id')
            ->pluck('payment_detail_id');

        $this->busyPaymentDetails = PaymentDetail::query() //TODO можно поменять местами сделки и реквизиты
            ->whereIn('id', $pendingOrdersIDs)
            ->with(['orders' => function ($query) {
                $query->where('status', OrderStatus::PENDING);
                $query->select('id', 'payment_detail_id', 'amount', 'currency', 'status');
            }])
            ->get(['id', 'payment_gateway_id', 'user_id', 'sub_payment_gateway_id']);
    }

    public function check(Detail $detail): bool
    {
        $amount = (int)$detail->finalAmount->toUnits();

        $unique = !$this->busyPaymentDetails
            ->where('payment_gateway_id', $detail->gateway->id)
            ->where('user_id', $detail->trader->id)
            ->pluck('orders')
            ->collapse()
            ->filter(function (Order $order) use ($amount) {
                return intval($order->amount->toUnits()) === $amount;
            })
            ->count();

        if (! $unique) {
            return false;
        }

        $unique = $this->uniqueByAmountForSBP($detail, $amount);

        return $unique;
    }

    protected function uniqueByAmountForSBP(Detail $detail, int $amount): bool
    {
        //Фильтры для СБП
        //1 Если метод сбп, то проверить что для под метода нет сделок с такой суммой
        //2 Если метод не сбп, то проверить что у сбп с таким под методом нет сделок с такой суммой
        if ($detail->gateway->isSBP) {
            $unique = !$this->busyPaymentDetails
                ->where('payment_gateway_id', $detail->subPaymentGatewayID)
                ->where('user_id', $detail->trader->id)
                ->pluck('orders')
                ->collapse()
                ->filter(function (Order $order) use ($amount) {
                    return intval($order->amount->toUnits()) === $amount;
                })
                ->count();
        } else {
            $unique = !$this->busyPaymentDetails
                ->where('sub_payment_gateway_id', $detail->paymentGatewayID)
                ->where('user_id', $detail->trader->id)
                ->pluck('orders')
                ->collapse()
                ->filter(function (Order $order) use ($amount) {
                    return intval($order->amount->toUnits()) === $amount;
                })
                ->count();
        }

        return $unique;
    }
}
