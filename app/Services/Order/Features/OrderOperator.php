<?php

namespace App\Services\Order\Features;

use App\Enums\BalanceType;
use App\Enums\DisputeStatus;
use App\Enums\OrderStatus;
use App\Enums\OrderSubStatus;
use App\Enums\TransactionType;
use App\Events\OrderReopenedFromFailedEvent;
use App\Events\OrderReopenedFromSucessfulEvent;
use App\Events\OrderFinishedAsFailedEvent;
use App\Events\OrderFinishedAsSuccessfulEvent;
use App\Exceptions\OrderException;
use App\Models\Order;
use App\Services\Money\Currency;
use App\Services\Money\Money;

class OrderOperator
{
    public function __construct(
        protected int $orderID
    )
    {}

    public function finishOrderAsSuccessful(OrderSubStatus $subStatus): void
    {
        $order = Order::where('id', $this->orderID)->lockForUpdate()->first();

        if ($order->status->notEquals(OrderStatus::PENDING)) {
            throw OrderException::orderAlreadyFinished($order);
        }

        $order->update([
            'status' => OrderStatus::SUCCESS,
            'sub_status' => $subStatus,
            'finished_at' => now()
        ]);

        OrderFinishedAsSuccessfulEvent::dispatch($order);
    }

    public function finishOrderAsFailed(OrderSubStatus $subStatus): void
    {
        $order = Order::where('id', $this->orderID)->lockForUpdate()->first();

        if ($order->status->notEquals(OrderStatus::PENDING)) {
            throw OrderException::orderAlreadyFinished($order);
        }

        $order->update([
            'status' => OrderStatus::FAIL,
            'sub_status' => $subStatus,
            'finished_at' => now()
        ]);

        OrderFinishedAsFailedEvent::dispatch($order);
    }

    public function reopenFinishedOrder(OrderSubStatus $subStatus): void
    {
        $order = Order::where('id', $this->orderID)->lockForUpdate()->first();

        if ($order->status->equals(OrderStatus::PENDING)) {
            throw OrderException::orderAlreadyOpened($order);
        }

        $status = $order->status;

        $order->update([
            'status' => OrderStatus::PENDING,
            'sub_status' => $subStatus,
            'finished_at' => null
        ]);

        if ($status->equals(OrderStatus::SUCCESS)) {
            OrderReopenedFromSucessfulEvent::dispatch($order);
        } else if ($status->equals(OrderStatus::FAIL)) {
            OrderReopenedFromFailedEvent::dispatch($order);
        }
    }

    public function updateAmount(Money $amount): void
    {
        /**
         * @var Order $order
         */
        $order = Order::where('id', $this->orderID)->lockForUpdate()->first();

        if (
            !(
                $order->dispute
                && $order->dispute->status->equals(DisputeStatus::PENDING)
                && $order->status->equals(OrderStatus::PENDING)
            )
        ) {
            throw OrderException::make('The order must be pending and has opened dispute.');
        }

        services()->wallet()->giveToBalance(
            $order->trader->wallet->id,
            $order->trader_paid_for_order,
            TransactionType::REFUND_FOR_CHANGE_ORDER_AMOUNT,
            BalanceType::TRUST
        );

        $profits = services()->profit()->calculateInBody(
            amount: $amount,
            exchangeRate: $order->conversion_price,
            totalCommissionRate: $order->total_service_commission_rate,
            traderCommissionRate: $order->trader_commission_rate,
            teamLeaderCommissionRate: $order->team_leader_commission_rate,
            teamLeaderSplitFromService: $this->resolveTeamLeaderSplitFromService(
                amount: $amount,
                exchangeRate: $order->conversion_price,
                totalCommissionRate: $order->total_service_commission_rate,
                teamLeaderCommissionRate: $order->team_leader_commission_rate,
                splitFromServicePercent: $order->team_leader_split_from_service_percent
            )
        );

        $amountUpdatesHistory = $order->amount_updates_history;

        $amountUpdatesHistory[] = [
            'old_amount' => $order->amount->toBeauty(),
            'new_amount' => $amount->toBeauty(),
            'by_user_id' => auth()->id(),
            'updated_at' => now()->toDateTimeString(),
        ];

        $traderPaidForOrder = $profits->totalProfit->sub($profits->traderProfit);

        services()->wallet()->takeFromBalance(
            $order->trader->wallet->id,
            $traderPaidForOrder,
            TransactionType::PAYMENT_FOR_CHANGE_ORDER_AMOUNT,
            BalanceType::TRUST
        );

        $rateFixedAt = $order->rate_fixed_at ?? now();
        $order->update([
            'amount' => $amount,
            'total_profit' => $profits->totalProfit,
            'merchant_profit' => $profits->merchantProfit,
            'service_profit' => $profits->serviceProfit,
            'trader_profit' => $profits->traderProfit,
            'team_leader_profit' => $profits->teamLeaderProfit,
            'trader_paid_for_order' => $traderPaidForOrder,
            'team_leader_split_from_service_percent' => $order->team_leader_split_from_service_percent,
            'team_leader_split_from_trader_percent' => $order->team_leader_split_from_trader_percent,
            'rate_fixed_at' => $rateFixedAt,
            'amount_updates_history' => $amountUpdatesHistory,
        ]);
    }

    private function resolveTeamLeaderSplitFromService(
        Money $amount,
        Money $exchangeRate,
        float $totalCommissionRate,
        float $teamLeaderCommissionRate,
        ?float $splitFromServicePercent
    ): ?Money {
        if ($splitFromServicePercent === null) {
            return null;
        }

        if ($totalCommissionRate <= 0 || $teamLeaderCommissionRate <= 0) {
            return null;
        }

        $totalProfit = $this->convertToUsdt($amount, $exchangeRate);
        $totalFee = $totalProfit->mul($totalCommissionRate / 100);
        $teamLeaderFee = $totalFee->mul($teamLeaderCommissionRate / $totalCommissionRate);

        return $teamLeaderFee->mul($splitFromServicePercent / 100);
    }

    private function convertToUsdt(Money $amount, Money $exchangeRate): Money
    {
        $usdtAmount = bcdiv(
            $amount->toPrecision(),
            $exchangeRate->toPrecision(),
            Money::DEFAULT_PRECISION
        );

        return Money::fromPrecision($usdtAmount, Currency::USDT()->getCode());
    }
}
