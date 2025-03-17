<?php

namespace App\Services\Order\Features;

use App\DTO\Order\AssignDetailsToOrderDTO;
use App\Enums\OrderStatus;
use App\Enums\OrderSubStatus;
use App\Events\DetailsAssignedToOrderEvent;
use App\Exceptions\OrderException;
use App\Models\Order;
use App\Services\Order\Features\OrderDetailProvider\OrderDetailProvider;

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
        $merchant = queries()->merchant()->findByID($this->order->merchant_id);

        $details = (new OrderDetailProvider(
            order: $this->order,
            merchant: $merchant,
            amount: $this->order->base_amount,
            currency: $this->data->gateway?->currency ?? $this->order->currency,
            gateway: $this->data->gateway,
            subGateway: $this->data->subGateway,
            detailType: $this->data->detailType,
        ))->provide();

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
            'trader_id' => $details->trader->id,
            'expires_at' => now()->addMinutes($details->gateway->reservationTime),
            'sub_status' => OrderSubStatus::WAITING_FOR_PAYMENT,
        ]);

        DetailsAssignedToOrderEvent::dispatch($this->order);

        return $this->order;
    }
}
