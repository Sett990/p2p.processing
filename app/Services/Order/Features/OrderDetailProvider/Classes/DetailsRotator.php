<?php

namespace App\Services\Order\Features\OrderDetailProvider\Classes;

use App\Enums\DetailType;
use App\Enums\MarketEnum;
use App\Enums\OrderStatus;
use App\Exceptions\OrderException;
use App\Models\PaymentDetail;
use App\Models\PaymentGateway;
use App\Models\ValueObjects\Settings\PrimeTimeSettings;
use App\Services\Money\Currency;
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
        protected ?PaymentGateway $subGateway = null,
        protected ?DetailType     $detailType = null,

    )
    {
        $this->primeTimeBonus = services()->settings()->getPrimeTimeBonus();
        $this->start = Carbon::createFromTimeString($this->primeTimeBonus->starts);
        $this->end = Carbon::createFromTimeString($this->primeTimeBonus->ends);
        $this->exchangePrice = services()->market()->getBuyPrice($this->amount->getCurrency(), $this->market);
    }

    public function throw(callable $callback): void
    {
        $pendingOrderCount = DB::table('orders')
            ->whereNotNull('payment_detail_id')
            ->where('status', OrderStatus::PENDING->value)
            ->select('payment_detail_id', DB::raw('count(*) as orders_count'))
            ->groupBy('payment_detail_id')
            ->get()
            ->pluck('orders_count', 'payment_detail_id')
            ->toArray();

        $this->queryPaymentDetails()
            ->chunk(100, function (Collection $paymentDetails) use ($callback, $pendingOrderCount) {
                $paymentDetails->each(function (PaymentDetail $paymentDetail) use ($callback, $pendingOrderCount) {
                    $count = isset($pendingOrderCount[$paymentDetail->id]) ? $pendingOrderCount[$paymentDetail->id] : 0;
                    if ($count >= $paymentDetail->max_pending_orders_quantity) {
                        return null;
                    }

                    $gateway = $this->gateways->where('id', $paymentDetail->payment_gateway_id)->first();
                    $trader = $this->traders->where('id', $paymentDetail->user_id)->first();

                    $detail = $this->makeDetail($paymentDetail, $gateway, $trader);

                    if (! $callback($detail)) {
                        return false;
                    }
                });
            });
    }

    protected function makeDetail(PaymentDetail $paymentDetail, Gateway $gateway, Trader $trader): Detail
    {
        //Trader Exchange Markup Rate
        $traderMarkupRate = $gateway->traderMarkupRate;

        if (now()->between($this->start, $this->end)) {
            $traderMarkupRate = round($traderMarkupRate + $this->primeTimeBonus->rate, 2);
        }

        //Exchange Price
        $exchangePriceWithMarkup = $this->exchangePrice->add(
            $this->exchangePrice->mul($traderMarkupRate / 100)
        );

        //Amounts
        $finalAmount = $gateway->amountWithServiceCommission;
        $profit = $gateway->amountWithServiceCommission
            ->convert($exchangePriceWithMarkup, Currency::USDT());
        $serviceProfit = $profit->mul($gateway->serviceCommissionRateTotal / 100);
        $merchantProfit = $profit->sub($serviceProfit);

        //Trader Exchange Markup
        $traderMarkup = $gateway->amountWithServiceCommission
            ->convert($this->exchangePrice, Currency::USDT())
            ->sub($profit);

        $profits = Profits::calculate(
            amount: $finalAmount,
            exchangeRate: $this->exchangePrice,
            totalCommissionRate: $gateway->serviceCommissionRateTotal,
            traderCommissionRate: $gateway->traderMarkupRate,
        );

        return new Detail(
            id: $paymentDetail->id,
            userID: $paymentDetail->user_id,
            paymentGatewayID: $paymentDetail->payment_gateway_id,
            subPaymentGatewayID: $paymentDetail->sub_payment_gateway_id,
            dailyLimit: $paymentDetail->daily_limit,
            currentDailyLimit: $paymentDetail->current_daily_limit,
            currency: $paymentDetail->currency,
            exchangePrice: $this->exchangePrice,
            profitTotal: $profits->amountConverted,
            profitServicePart: $profits->serviceCommission,
            profitMerchantPart: $profits->merchantProfit,
            traderMarkupRate: $traderMarkupRate,
            traderMarkup: $profits->traderCommission,
            gateway: $gateway,
            trader: $trader,
            initialAmount: $this->amount,
            finalAmount: $finalAmount,
        );
    }

    protected function queryPaymentDetails(): Builder
    {
        return PaymentDetail::query()
            ->whereNull('archived_at')
            /*->withCount(['orders' => function ($query) {
                $query->where('status', OrderStatus::PENDING);
            }])*/
            ->whereIn('user_id', $this->traders->pluck('id'))
            ->whereIn('payment_gateway_id', $this->gateways->pluck('id'))
            ->when($this->subGateway, function (Builder $query) {
                $query->where('sub_payment_gateway_id', $this->subGateway->id);
            })
            ->when($this->detailType, function (Builder $query) {
                $query->where('detail_type', $this->detailType);
            })
            ->active()
            /*->select([
                'id', 'user_id', 'payment_gateway_id', 'sub_payment_gateway_id', 'daily_limit', 'current_daily_limit', 'currency', 'max_pending_orders_quantity', 'last_used_at'
            ])*/
            ->orderBy('last_used_at');
    }
}
