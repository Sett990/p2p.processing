<?php

namespace App\Services\Order\Features;

use App\DTO\Order\AssignDetailsToOrderDTO;
use App\Enums\BalanceType;
use App\Enums\MarketEnum;
use App\Enums\OrderStatus;
use App\Enums\OrderSubStatus;
use App\Enums\TransactionType;
use App\Events\DetailsAssignedToOrderEvent;
use App\Exceptions\OrderException;
use App\Models\Order;
use App\Services\Money\Money;
use App\Services\Order\Features\OrderDetailProvider\OrderDetailProvider;
use App\Services\Order\Utils\DailyLimit;
use Illuminate\Support\Carbon;

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
        $teamLeaderSplitFromTraderPercent = $teamLeaderSplitFromServicePercent !== null
            ? max(0, 100 - $teamLeaderSplitFromServicePercent)
            : null;
        $teamLeaderSplitFromService = $this->resolveTeamLeaderSplitFromService(
            amount: $details->amount,
            exchangeRate: $details->exchangePrice,
            totalCommissionRate: $details->gateway->serviceCommissionRate,
            teamLeaderCommissionRate: $details->teamLeaderCommissionRate,
            splitFromServicePercent: $teamLeaderSplitFromServicePercent
        );

        $profits = services()->profit()->calculateInBody(
            amount: $details->amount,
            exchangeRate: $details->exchangePrice,
            totalCommissionRate: $details->gateway->serviceCommissionRate,
            traderCommissionRate: $details->traderCommissionRate,
            teamLeaderCommissionRate: $details->teamLeaderCommissionRate,
            teamLeaderSplitFromService: $teamLeaderSplitFromService
        );
        $traderPaidForOrder = $profits->totalProfit->sub($profits->traderProfit);
        $calcMeta = $this->buildCalcMeta(
            amount: $details->amount,
            exchangePrice: $details->exchangePrice,
            market: $this->order->market,
            rateFixedAt: $rateFixedAt,
            totalCommissionRate: $details->gateway->serviceCommissionRate,
            traderCommissionRate: $details->traderCommissionRate,
            teamLeaderCommissionRate: $details->teamLeaderCommissionRate,
            calc: $profits,
            teamLeaderSplitFromServicePercent: $teamLeaderSplitFromServicePercent,
            teamLeaderSplitFromTraderPercent: $teamLeaderSplitFromTraderPercent,
            logic: 'IN_BODY'
        );

        $this->order->update([
            'amount' => $details->amount,
            'total_profit' => $profits->totalProfit,
            'merchant_profit' => $profits->merchantProfit,
            'service_profit' => $profits->serviceProfit,
            'trader_profit' => $profits->traderProfit,
            'team_leader_profit' => $profits->teamLeaderProfit,
            'trader_paid_for_order' => $traderPaidForOrder,
            'team_leader_split_from_service' => $profits->teamLeaderSplitFromService,
            'team_leader_split_from_trader' => $profits->teamLeaderSplitFromTrader,
            'team_leader_split_from_service_percent' => $teamLeaderSplitFromServicePercent,
            'team_leader_split_from_trader_percent' => $teamLeaderSplitFromTraderPercent,
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
            'calc_meta' => $calcMeta,
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

    private function buildCalcMeta(
        Money $amount,
        Money $exchangePrice,
        MarketEnum $market,
        Carbon $rateFixedAt,
        float $totalCommissionRate,
        float $traderCommissionRate,
        float $teamLeaderCommissionRate,
        object $calc,
        ?float $teamLeaderSplitFromServicePercent,
        ?float $teamLeaderSplitFromTraderPercent,
        string $logic
    ): array {
        $traderReceive = property_exists($calc, 'traderReceive')
            ? $calc->traderReceive
            : $calc->traderProfit;
        $serviceCommissionRate = max(
            $totalCommissionRate - $traderCommissionRate - $teamLeaderCommissionRate,
            0
        );
        $traderDebit = $logic === 'IN_BODY'
            ? $calc->totalProfit->sub($calc->traderProfit)
            : null;

        return [
            'logic' => $logic,
            'inputs' => [
                'amount' => $amount->toPrecision(),
                'amount_currency' => strtoupper($amount->getCurrency()->getCode()),
                'total_commission_rate' => $totalCommissionRate,
                'trader_commission_rate' => $traderCommissionRate,
                'teamlead_commission_rate' => $teamLeaderCommissionRate,
                'service_commission_rate' => $serviceCommissionRate,
            ],
            'exchange' => [
                'market' => $market->value,
                'price' => $exchangePrice->toPrecision(),
                'currency' => strtoupper($exchangePrice->getCurrency()->getCode()),
                'fixed_at' => $rateFixedAt->toIso8601String(),
            ],
            'outputs' => [
                'usdt_body' => $calc->totalProfit->toPrecision(),
                'total_fee' => $calc->totalFee->toPrecision(),
                'trader_fee_base' => $calc->traderFeeBase->toPrecision(),
                'trader_fee' => $calc->traderProfit->toPrecision(),
                'teamlead_fee' => $calc->teamLeaderProfit->toPrecision(),
                'service_fee_base' => $calc->serviceFeeBase->toPrecision(),
                'service_fee' => $calc->serviceProfit->toPrecision(),
                'merchant_pay' => $logic === 'OUT_BODY' ? $calc->merchantProfit->toPrecision() : null,
                'merchant_credit' => $logic === 'IN_BODY' ? $calc->merchantProfit->toPrecision() : null,
                'trader_receive' => $traderReceive->toPrecision(),
                'trader_debit' => $traderDebit?->toPrecision(),
            ],
            'split' => [
                'from_service' => $calc->teamLeaderSplitFromService?->toPrecision(),
                'from_trader' => $calc->teamLeaderSplitFromTrader?->toPrecision(),
                'from_service_percent' => $teamLeaderSplitFromServicePercent,
                'from_trader_percent' => $teamLeaderSplitFromTraderPercent,
            ],
        ];
    }

    private function resolveTeamLeaderSplitFromService(
        Money $amount,
        Money $exchangeRate,
        float $totalCommissionRate,
        float $teamLeaderCommissionRate,
        float $splitFromServicePercent
    ): ?Money {
        if ($totalCommissionRate <= 0 || $teamLeaderCommissionRate <= 0) {
            return null;
        }

        $totalProfit = $amount->div($exchangeRate);
        $totalFee = $totalProfit->mul($totalCommissionRate / 100);
        $teamLeaderFee = $totalFee->mul($teamLeaderCommissionRate / $totalCommissionRate);

        return $teamLeaderFee->mul($splitFromServicePercent / 100);
    }
}
