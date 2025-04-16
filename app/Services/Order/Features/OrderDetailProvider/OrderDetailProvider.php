<?php

namespace App\Services\Order\Features\OrderDetailProvider;

use App\Enums\DetailType;
use App\Exceptions\OrderException;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\PaymentDetail;
use App\Models\PaymentGateway;
use App\Services\Money\Currency;
use App\Services\Money\Money;
use App\Services\Order\Features\OrderDetailProvider\Classes\DetailsRotator;
use App\Services\Order\Features\OrderDetailProvider\Classes\GatewaysProvider;
use App\Services\Order\Features\OrderDetailProvider\Classes\TradersProvider;
use App\Services\Order\Features\OrderDetailProvider\Filters\UniqueAmount;
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
        protected ?DetailType $detailType = null,
    )
    {
        $this->gatewaysProvider = new GatewaysProvider(
            $this->merchant,
            $this->amount,
            $this->currency,
            $this->gateway,
        );

        $this->tradersProvider = (new TradersProvider($this->merchant, $this->amount, $this->order->market, $this->detailType));

        $this->filtersList = [
            new UniqueAmount(),
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
            $this->detailType,
        );

        $selectedDetail = null;

        $start = microtime(true);

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

        $end = microtime(true);
        $executionTime = $end - $start;
        dump("Execution time: {$executionTime} seconds");

        if (! $selectedDetail) {
            throw OrderException::make('Подходящие платежные реквизиты не найдены.');
        }

        $paymentDetail = PaymentDetail::where('id', $selectedDetail->id)->lockForUpdate()->first();
        $paymentDetail->update([
            'last_used_at' => now()
        ]);

        return $selectedDetail;
    }
}
