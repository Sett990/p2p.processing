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
        $merchant = queries()->merchant()->findByID($this->order->merchant_id);

        $details = (new OrderDetailProvider(
            order: $this->order,
            merchant: $merchant,
            amount: $this->order->base_amount,
            currency: $this->data->gateway?->currency ?? $this->order->currency,
            gateway: $this->data->gateway,
            detailType: $this->data->detailType,
        ))->provide();

        $rateFixedAt = now();
        $teamLeaderSplitFromServicePercent = $details->trader->teamLeaderSplitFromServicePercent;
        $profits = services()->profit()->calculateInBody(
            amount: $details->amount,
            exchangeRate: $details->exchangePrice,
            totalCommissionRate: $details->gateway->serviceCommissionRate,
            traderCommissionRate: $details->traderCommissionRate,
            teamLeaderCommissionRate: $details->teamLeaderCommissionRate,
            teamLeaderSplitFromServicePercent: $teamLeaderSplitFromServicePercent
        );
        $traderPaidForOrder = $profits->totalProfit->sub($profits->traderProfit);
        $this->order->update([
            'amount' => $details->amount,
            'total_profit' => $profits->totalProfit,
            'merchant_profit' => $profits->merchantProfit,
            'service_profit' => $profits->serviceProfit,
            'trader_profit' => $profits->traderProfit,
            'team_leader_profit' => $profits->teamLeaderProfit,
            'trader_paid_for_order' => $traderPaidForOrder,
            'team_leader_split_from_service_percent' => $teamLeaderSplitFromServicePercent,
            'conversion_price' => $details->exchangePrice,
            'rate_fixed_at' => $rateFixedAt,
            'trader_commission_rate' => $details->traderCommissionRate,
            'team_leader_commission_rate' => $details->teamLeaderCommissionRate,
            'total_service_commission_rate' => $details->gateway->serviceCommissionRate,
            'payment_gateway_id' => $details->gateway->id,
            'payment_detail_id' => $details->id,
            'trader_id' => $details->trader->id,
            'team_leader_id' => $details->trader->teamLeaderID,
            'expires_at' => now()->addMinutes($details->gateway->reservationTime),
            'sub_status' => OrderSubStatus::WAITING_FOR_PAYMENT,
        ]);

        DailyLimit::increment($this->order->payment_detail_id, $this->order->amount, $this->order->created_at);

        services()->wallet()->takeFromBalance(
            $this->order->trader->wallet->id,
            $this->order->trader_paid_for_order,
            TransactionType::PAYMENT_FOR_OPENED_ORDER,
            BalanceType::TRUST
        );

        DetailsAssignedToOrderEvent::dispatch($this->order);

        return $this->order;
    }

}
