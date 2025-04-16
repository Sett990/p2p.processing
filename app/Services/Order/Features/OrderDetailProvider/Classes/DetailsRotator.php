<?php

namespace App\Services\Order\Features\OrderDetailProvider\Classes;

use App\Enums\DetailType;
use App\Enums\MarketEnum;
use App\Enums\OrderStatus;
use App\Models\PaymentDetail;
use App\Models\ValueObjects\Settings\PrimeTimeSettings;
use App\Services\Money\Money;
use App\Services\Order\BusinesLogic\Profits;
use App\Services\Order\Features\OrderDetailProvider\Values\Detail;
use App\Services\Order\Features\OrderDetailProvider\Values\Gateway;
use App\Services\Order\Features\OrderDetailProvider\Values\Trader;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DetailsRotator
{
    protected PrimeTimeSettings $primeTimeBonus;
    protected Carbon $start;
    protected Carbon $end;
    protected Money $exchangePrice;

    public function __construct(
        protected MarketEnum      $market,
        protected Collection      $gateways,
        protected Collection      $traders,
        protected Money           $amount,
        protected ?DetailType     $detailType = null,
    )
    {
        $this->primeTimeBonus = services()->settings()->getPrimeTimeBonus();
        $this->start = Carbon::createFromTimeString($this->primeTimeBonus->starts);
        $this->end = Carbon::createFromTimeString($this->primeTimeBonus->ends);
        $this->exchangePrice = services()->market()->getBuyPrice($this->amount->getCurrency(), $this->market);
    }

    public function throw(): ?Detail
    {
        $pendingOrderCount = DB::table('orders')
            ->whereNotNull('payment_detail_id')
            ->where('status', OrderStatus::PENDING->value)
            ->select('payment_detail_id', DB::raw('count(*) as orders_count'))
            ->groupBy('payment_detail_id')
            ->get()
            ->pluck('orders_count', 'payment_detail_id')
            ->toArray();

        $detail = null;

        $this->queryPaymentDetails()
            ->chunk(100, function (Collection $paymentDetails) use ($pendingOrderCount, &$detail) {
                $paymentDetails->each(function (PaymentDetail $paymentDetail) use ($pendingOrderCount, &$detail) {
                    $count = isset($pendingOrderCount[$paymentDetail->id]) ? $pendingOrderCount[$paymentDetail->id] : 0;
                    if ($count >= $paymentDetail->max_pending_orders_quantity) {
                        return null;
                    }

                    $randomGatewayID = $paymentDetail->paymentGateways->pluck('id')->random();

                    $gateway = $this->gateways->where('id', $randomGatewayID)->first();
                    $trader = $this->traders->where('id', $paymentDetail->user_id)->first();

                    $detail = $this->makeDetail($paymentDetail, $gateway, $trader);

                    return false;
                });

                return !$detail;
            });

        return $detail;
    }

    protected function makeDetail(PaymentDetail $paymentDetail, Gateway $gateway, Trader $trader): Detail
    {
        //Trader Commission Rate Prime Time
        $traderCommissionRate = $gateway->traderCommissionRate;

        if (now()->between($this->start, $this->end)) {
            $traderCommissionRate = round($traderCommissionRate + $this->primeTimeBonus->rate, 2);
        }

        $teamLeaderCommissionRate = $trader->teamLeaderCommissionRate;

        //Profits
        $profits = Profits::calculate(
            amount: $this->amount,
            exchangeRate: $this->exchangePrice,
            totalCommissionRate: $gateway->serviceCommissionRate,
            traderCommissionRate: $traderCommissionRate,
            teamLeaderCommissionRate: $teamLeaderCommissionRate,
        );

        $traderPaidForOrder = $profits->totalProfit->sub($profits->traderProfit);

        return new Detail(
            id: $paymentDetail->id,
            userID: $paymentDetail->user_id,
            paymentGatewayID: $gateway->id,
            userDeviceID: $paymentDetail->user_device_id,
            dailyLimit: $paymentDetail->daily_limit,
            currentDailyLimit: $paymentDetail->current_daily_limit,
            currency: $paymentDetail->currency,
            exchangePrice: $this->exchangePrice,
            totalProfit: $profits->totalProfit,
            serviceProfit: $profits->serviceProfit,
            merchantProfit: $profits->merchantProfit,
            traderProfit: $profits->traderProfit,
            teamLeaderProfit: $profits->teamLeaderProfit,
            traderCommissionRate: $traderCommissionRate,
            teamLeaderCommissionRate: $teamLeaderCommissionRate,
            traderPaidForOrder: $traderPaidForOrder,
            gateway: $gateway,
            trader: $trader,
            amount: $this->amount,
        );
    }

    protected function queryPaymentDetails(): Builder
    {
        return PaymentDetail::query()
            ->with('paymentGateways:id')
            ->whereNull('archived_at')
            ->whereIn('user_id', $this->traders->pluck('id'))
            ->whereHas('paymentGateways', function ($query) {
                $query->whereIn('payment_gateways.id', $this->gateways->pluck('id'));
            })
            ->whereRaw('(daily_limit - current_daily_limit) >= ?', [$this->amount->toUnitsInt()])
            ->where(function ($query) {
                // Проверяем, что сумма сделки больше или равна минимальной сумме сделки
                // или минимальная сумма сделки равна нулю или NULL (не установлена)
                $query->where(function ($q) {
                    $q->whereNull('min_order_amount')
                      ->orWhere('min_order_amount', 0)
                      ->orWhere('min_order_amount', '<=', $this->amount->toUnitsInt());
                });

                // Проверяем, что сумма сделки меньше или равна максимальной сумме сделки
                // или максимальная сумма сделки равна нулю или NULL (не установлена)
                $query->where(function ($q) {
                    $q->whereNull('max_order_amount')
                      ->orWhere('max_order_amount', 0)
                      ->orWhere('max_order_amount', '>=', $this->amount->toUnitsInt());
                });
            })
            ->when($this->detailType, function (Builder $query) {
                $query->where('detail_type', $this->detailType);
            })
            // Проверяем интервал между сделками
            ->where(function ($query) {
                $query->whereNull('order_interval_minutes')
                    ->orWhere('order_interval_minutes', 0)
                    ->orWhereRaw('TIMESTAMPDIFF(MINUTE, last_used_at, ?) >= order_interval_minutes', [now()])
                    ->orWhereNull('last_used_at');
            })
            // Фильтрация по уникальности суммы за последние 10 минут
            ->whereDoesntHave('orders', function ($query) {
                $query->where('status', OrderStatus::SUCCESS)
                    ->where('finished_at', '>=', now()->subMinutes(10))
                    ->where('amount', '>=', $this->amount->mul(0.95)->toUnitsInt())
                    ->where('amount', '<=', $this->amount->mul(1.05)->toUnitsInt());
            })
            // Уникальность суммы для PENDING заказов
            ->whereDoesntHave('orders', function ($query) {
                $query->where('status', OrderStatus::PENDING)
                    ->where('amount', $this->amount->toUnitsInt());
            })
            ->active()
            ->orderBy('last_used_at');
    }
}
