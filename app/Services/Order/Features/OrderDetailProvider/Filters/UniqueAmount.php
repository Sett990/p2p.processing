<?php

namespace App\Services\Order\Features\OrderDetailProvider\Filters;

use App\Models\Order;
use App\Services\Order\Features\OrderDetailProvider\Values\Detail;
use Illuminate\Database\Eloquent\Collection;

class UniqueAmount extends BaseFilter
{
    public function __construct(
        protected Collection $paymentDetails
    )
    {}

    public function check(Detail $detail): bool
    {
        $amount = (int)$detail->finalAmount->toUnits();

        $unique = !$this->paymentDetails
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
            $unique = !$this->paymentDetails
                ->where('payment_gateway_id', $detail->subPaymentGatewayID)
                ->where('user_id', $detail->trader->id)
                ->pluck('orders')
                ->collapse()
                ->filter(function (Order $order) use ($amount) {
                    return intval($order->amount->toUnits()) === $amount;
                })
                ->count();
        } else {
            $unique = !$this->paymentDetails
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
