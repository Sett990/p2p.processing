<?php

namespace App\Services\Order\Features\OrderDetailProvider;

use App\Enums\DetailType;
use App\Exceptions\OrderException;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\PaymentGateway;
use App\Services\Money\Currency;
use App\Services\Money\Money;
use App\Services\Order\Features\OrderDetailProvider\Classes\DetailsRotator;
use App\Services\Order\Features\OrderDetailProvider\Classes\GatewaysProvider;
use App\Services\Order\Features\OrderDetailProvider\Classes\TradersProvider;
use App\Services\Order\Features\OrderDetailProvider\Filters\DailyLimitFilter;
use App\Services\Order\Features\OrderDetailProvider\Filters\UniqueAmount;
use App\Services\Order\Features\OrderDetailProvider\Filters\TrustBalance;
use App\Services\Order\Features\OrderDetailProvider\Filters\UniqueAmountByLatestFinishedOrders;
use App\Services\Order\Features\OrderDetailProvider\Values\Detail;

class OrderDetailProvider
{
    protected GatewaysProvider $gatewaysProvider;
    protected TradersProvider $tradersProvider;
    protected array $filtersList;

    public function __construct(
        protected Order $order,
        protected Merchant $merchant,
        protected Money $amount,
        protected ?Currency $currency = null,
        protected ?PaymentGateway $gateway = null,
        protected ?PaymentGateway $subGateway = null,
        protected ?DetailType $detailType = null,
    )
    {
        $this->gatewaysProvider = new GatewaysProvider(
            $this->merchant,
            $this->amount,
            $this->currency,
            $this->gateway,
        );

        $this->tradersProvider = new TradersProvider($this->order->market, $this->detailType);

        $this->filtersList = [
            new UniqueAmount(),
            new TrustBalance(),
            new DailyLimitFilter(),
            new UniqueAmountByLatestFinishedOrders($this->amount),
        ];
    }

    /**
     * @throws OrderException
     */
    public function provide(): Detail
    {
        $gateways = $this->gatewaysProvider->get();

        $traders = $this->tradersProvider->get($gateways);

        $detailsRotator = new DetailsRotator(
            $this->order->market,
            $gateways,
            $traders,
            $this->amount,
            $this->subGateway,
            $this->detailType,
        );

        $selectedDetail = null;

        $detailsRotator->throw(function (Detail $detail) use (&$selectedDetail) {
            $isOk = true;

            foreach ($this->filtersList as $filter) {
                if (! $filter->check($detail)) {
                    $isOk = false;
                    break;
                }
            }

            if ($isOk) {
                $selectedDetail = $detail;
                return false;
            }

            return true;
        });

        if (! $selectedDetail) {
            throw OrderException::make('Подходящие платежные реквизиты не найдены.');
        }

        return $selectedDetail;
    }
}
