<?php

namespace App\Services\Order\Features\OrderDetailProvider;

use App\Enums\DetailType;
use App\Enums\OrderStatus;
use App\Exceptions\OrderException;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\PaymentDetail;
use App\Models\PaymentGateway;
use App\Models\User;
use App\Services\Money\Currency;
use App\Services\Money\Money;
use App\Services\Order\Features\OrderDetailProvider\Filters\DailyLimitFilter;
use App\Services\Order\Features\OrderDetailProvider\Filters\UniqueAmount;
use App\Services\Order\Features\OrderDetailProvider\Filters\TrustBalance;
use App\Services\Order\Features\OrderDetailProvider\Values\Gateway;
use App\Services\Order\Features\OrderDetailProvider\Values\Trader;
use App\Services\Order\Features\OrderDetailProvider\Values\Detail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class OrderDetailProvider
{
    public function __construct(
        protected Merchant $merchant,
        protected Money $amount,
        protected ?Currency $currency = null,
        protected ?PaymentGateway $gateway = null,
        protected ?PaymentGateway $subGateway = null,
        protected ?DetailType $detailType = null,
    )
    {}

    /**
     * @throws OrderException
     */
    public function provide(): Detail
    {
        $gateways = $this->getGateways();

        $traders = $this->getTraders($gateways);

        $details = $this->getDetails($gateways, $traders);

        $details = $this->filterDetails($details);

        if ($details->isEmpty()) {
            throw OrderException::make('Подходящие платежные реквизиты не найдены.');
        }

        return $details->random();
    }

    /**
     * @param Collection<int, Detail> $details
     * @return Collection<int, Detail>
     */
    public function filterDetails(Collection $details): Collection
    {
        $pendingOrdersIDs = Order::query()
            ->where('status', OrderStatus::PENDING)
            ->get('payment_detail_id')
            ->pluck('payment_detail_id');

        $paymentDetails = PaymentDetail::query() //TODO можно поменять местами сделки и реквизиты
            ->whereIn('id', $pendingOrdersIDs)
            ->with(['orders' => function ($query) {
                $query->where('status', OrderStatus::PENDING);
                $query->select('id', 'payment_detail_id', 'amount', 'currency', 'status');
            }])
            ->get(['id', 'payment_gateway_id', 'user_id', 'sub_payment_gateway_id']);

        $filters = [
            new UniqueAmount($paymentDetails),
            new TrustBalance(),
            new DailyLimitFilter()
        ];

        $details = $details->filter(function (Detail $detail) use ($filters) {
            foreach ($filters as $filter) {
                if (! $filter->check($detail)) {
                    return false;
                }
            }

            return true;
        });

        return $details;
    }

    /**
     * @return Collection<int, Gateway>
     * @throws OrderException
     */
    protected function getGateways(): Collection
    {
        if ($this->gateway) {
            $paymentGateways = queries()
                ->paymentGateway()
                ->getByCodesForOrderCreate([$this->gateway->code], $this->amount);
        } else if ($this->currency) {
            $paymentGateways = queries()
                ->paymentGateway()
                ->getByCurrencyForOrderCreate($this->currency, $this->amount);
        } else {
            throw OrderException::make('Требуется валюта или платежный метод.');
        }

        if ($paymentGateways->isEmpty()) {
            throw OrderException::make('Подходящий платежный метод не найден для данных лимитов/валюты.');
        }

        $gateways = collect();

        $paymentGateways->each(function (PaymentGateway $gateway) use (&$gateways) {
            $commission = services()->commission()->getOrderServiceCommissionRate($gateway, $this->merchant);

            $amount = $this->amount;
            if ($commission->client > 0) {
                $clientCommissionAmount = $this->amount
                    ->mul($commission->client / 100);

                $amount = $this->amount->add(
                    intval(round($clientCommissionAmount->toBeauty()))
                );
            }

            $gateways->push(
                new Gateway(
                    id: $gateway->id,
                    code: $gateway->code,
                    reservationTime: $gateway->reservation_time,
                    amountWithServiceCommission: $amount,
                    traderMarkupRate: $gateway->buy_price_markup_rate,
                    serviceCommissionRateTotal: $commission->total,
                    serviceCommissionRateMerchant: $commission->merchant,
                    serviceCommissionRateClient: $commission->client,
                    isSBP: $gateway->is_sbp
                )
            );
        });

        return $gateways;
    }

    /**
     * @param Collection<int, Gateway> $gateways
     * @return Collection<int, Trader>
     */
    protected function getTraders(Collection $gateways): Collection
    {
        $traders = collect();

        User::query()
            ->with(['wallet' => function (HasOne $query) {
                $query->select(['user_id', 'trust_balance', 'currency']);
            }])
            ->where('is_online', true)
            ->whereNull('banned_at')
            ->whereHas('paymentDetails', function ($query) use ($gateways) {
                $query->active();
                $query->whereIn('payment_gateway_id', $gateways->pluck('id')->toArray());
                $query->when($this->detailType, function (Builder $query) {
                    $query->where('detail_type', $this->detailType);
                });
            })
            ->select([
                'id'
            ])
            ->get()
            ->each(function (User $user) use (&$traders) {
                $traders->push(
                    new Trader(
                        id: $user->id,
                        trustBalance: $user->wallet->trust_balance,
                    )
                );
            });

        return $traders;
    }

    /**
     * @param Collection<int, Gateway> $gateways
     * @param Collection<int, Trader> $traders
     * @return Collection<int, Detail>
     */
    protected function getDetails(Collection $gateways, Collection $traders): Collection
    {
        $busyDetailIDs = Order::query()
            ->where('status', OrderStatus::PENDING)
            ->get('payment_detail_id')
            ->pluck('payment_detail_id')
            ->toArray();

        $paymentDetails = PaymentDetail::query()
            ->whereNotIn('id', $busyDetailIDs)
            ->whereIn('user_id', $traders->pluck('id'))
            ->whereIn('payment_gateway_id', $gateways->pluck('id'))
            ->when($this->subGateway, function (Builder $query) {
                $query->where('sub_payment_gateway_id', $this->subGateway->id);
            })
            ->when($this->detailType, function (Builder $query) {
                $query->where('detail_type', $this->detailType);
            })
            ->active()
          /*  ->whereDoesntHave('orders', function (Builder $query) use ($gateways, $traders) {
                $query->where('status', OrderStatus::PENDING);
            })*/
            ->select([
                'id', 'user_id', 'payment_gateway_id', 'sub_payment_gateway_id', 'daily_limit', 'current_daily_limit', 'currency'
            ])
            ->get();

        $details = collect();

        $primeTimeBonus = services()->settings()->getPrimeTimeBonus();
        $start = Carbon::createFromTimeString($primeTimeBonus->starts);
        $end = Carbon::createFromTimeString($primeTimeBonus->ends);

        $baseExchangePrice = services()->market()->getBuyPrice($this->amount->getCurrency());

        $paymentDetails->each(function (PaymentDetail $detail) use ($gateways, $traders, &$details, $primeTimeBonus, $start, $end, $baseExchangePrice) {
            /**
             * @var Gateway $gateway
             * @var Trader $trader
             */
            $gateway = $gateways->where('id', $detail->payment_gateway_id)->first();
            $trader = $traders->where('id', $detail->user_id)->first();

            //Trader Exchange Markup Rate
            $traderMarkupRate = $gateway->traderMarkupRate;

            if (now()->between($start, $end)) {
                $traderMarkupRate = round($traderMarkupRate + $primeTimeBonus->rate, 2);
            }

            //Exchange Price
            $exchangePriceWithMarkup = $baseExchangePrice->add(
                $baseExchangePrice->mul($traderMarkupRate / 100)
            );

            //Amounts
            $finalAmount = $gateway->amountWithServiceCommission;
            $profit = $gateway->amountWithServiceCommission
                ->convert($exchangePriceWithMarkup, Currency::USDT());
            $serviceProfit = $profit->mul($gateway->serviceCommissionRateTotal / 100);
            $merchantProfit = $profit->sub($serviceProfit);

            //Trader Exchange Markup
            $traderMarkup = $gateway->amountWithServiceCommission
                ->convert($baseExchangePrice, Currency::USDT())
                ->sub($profit);

            $details->push(
                new Detail(
                    id: $detail->id,
                    userID: $detail->user_id,
                    paymentGatewayID: $detail->payment_gateway_id,
                    subPaymentGatewayID: $detail->sub_payment_gateway_id,
                    dailyLimit: $detail->daily_limit,
                    currentDailyLimit: $detail->current_daily_limit,
                    currency: $detail->currency,
                    exchangePriceInitial: $baseExchangePrice,
                    exchangePriceWithMarkup: $exchangePriceWithMarkup,
                    profitTotal: $profit,
                    profitServicePart: $serviceProfit,
                    profitMerchantPart: $merchantProfit,
                    traderMarkupRate: $traderMarkupRate,
                    traderMarkup: $traderMarkup,
                    gateway: $gateway,
                    trader: $trader,
                    initialAmount: $this->amount,
                    finalAmount: $finalAmount,
                )
            );
        });

        return $details;
    }
}
