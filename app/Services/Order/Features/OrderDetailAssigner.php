<?php

namespace App\Services\Order\Features;

use App\DTO\Order\AssignDetailsToOrderDTO;
use App\Enums\BalanceType;
use App\Enums\OrderStatus;
use App\Enums\OrderSubStatus;
use App\Enums\TransactionType;
use App\Events\DetailsAssignedToOrderEvent;
use App\Exceptions\OrderException;
use App\Models\Order;
use App\Models\PaymentDetail;
use App\Services\Order\Features\OrderDetailProvider\OrderDetailProvider;
use App\Services\Order\Utils\DailyLimit;

class OrderDetailAssigner
{
    public function __construct(
        protected Order $order,
        protected AssignDetailsToOrderDTO $data
    )
    {
        if ($this->order->status->notEquals(OrderStatus::PENDING)) {
            throw OrderException::orderIsFinished($this->order);
        }
    }

    public function assign(): Order
    {
        $details = (new OrderDetailProvider(
            order: $this->order,
            merchant: $this->order->merchant,
            amount: $this->order->base_amount,
            currency: $this->data->gateway?->currency ?? $this->order->currency,
            gateway: $this->data->gateway,
            subGateway: $this->data->subGateway,
            detailType: $this->data->detailType,
        ))->provide();

        $paymentDetail = PaymentDetail::find($details->id);

        //TODO move to listeners
        (new DailyLimit(
            paymentDetail: $paymentDetail,
            amount: $details->amount
        ))->increment();

        //TODO move to listeners
        services()->wallet()->takeFromBalance(
            $paymentDetail->user->wallet,
            $details->traderPaidForOrder,
            TransactionType::PAYMENT_FOR_OPENED_ORDER,
            BalanceType::TRUST
        );

        $this->order->update([
            'amount' => $details->amount,
            'total_profit' => $details->totalProfit,
            'merchant_profit' => $details->merchantProfit,
            'service_profit' => $details->serviceProfit,
            'trader_profit' => $details->traderProfit,
            'trader_paid_for_order' => $details->traderPaidForOrder,
            'conversion_price' => $details->exchangePrice,
            'trader_commission_rate' => $details->traderCommissionRate,
            'total_service_commission_rate' => $details->gateway->serviceCommissionRate,
            'payment_gateway_id' => $details->gateway->id,
            'payment_detail_id' => $details->id,
            'trader_id' => $paymentDetail->user_id,
            'expires_at' => now()->addMinutes($details->gateway->reservationTime),
            'sub_status' => OrderSubStatus::WAITING_FOR_PAYMENT,
        ]);

        $paymentDetail->update([
            'last_used_at' => now()
        ]);

        DetailsAssignedToOrderEvent::dispatch($this->order);

        return $this->order;
    }
}
