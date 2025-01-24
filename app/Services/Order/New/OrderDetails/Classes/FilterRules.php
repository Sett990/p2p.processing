<?php

namespace App\Services\Order\OrderDetails\Classes;

use App\Models\Order;
use App\Services\Order\OrderDetails\Values\Detail;
use Illuminate\Database\Eloquent\Collection;

class FilterRules
{
    public function uniqueByAmount(Collection $paymentDetails, Detail $detail, int $amount): bool
    {
        $unique = !$paymentDetails
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

        $unique = $this->uniqueByAmountForSBP($paymentDetails, $detail, $amount);

        return $unique;
    }

    public function uniqueByAmountForSBP(Collection $paymentDetails, Detail $detail, int $amount): bool
    {
        //Фильтры для СБП
        //1 Если метод сбп, то проверить что для под метода нет сделок с такой суммой
        //2 Если метод не сбп, то проверить что у сбп с таким под методом нет сделок с такой суммой
        if ($detail->gateway->isSBP) {
            $unique = !$paymentDetails
                ->where('payment_gateway_id', $detail->subPaymentGatewayID)
                ->where('user_id', $detail->trader->id)
                ->pluck('orders')
                ->collapse()
                ->filter(function (Order $order) use ($amount) {
                    return intval($order->amount->toUnits()) === $amount;
                })
                ->count();
        } else {
            $unique = !$paymentDetails
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
