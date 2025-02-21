<?php

namespace App\Services\Order\Features\OrderDetailProvider\Classes;

use App\Enums\DetailType;
use App\Exceptions\OrderException;
use App\Models\Merchant;
use App\Models\PaymentGateway;
use App\Services\Money\Currency;
use App\Services\Money\Money;
use App\Services\Order\Features\OrderDetailProvider\Values\Gateway;
use Illuminate\Support\Collection;

class GatewaysProvider
{
    public function __construct(
        protected Merchant $merchant,
        protected Money $amount,
        protected ?Currency $currency = null,
        protected ?PaymentGateway $gateway = null,
    ){}

    /**
     * @return Collection<int, Gateway>
     * @throws OrderException
     */
    public function get(): Collection
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

        $gatewaySettings = $this->merchant->gateway_settings;

        $paymentGateways = $paymentGateways->filter(function (PaymentGateway $paymentGateway) use ($gatewaySettings) {
            return isset($gatewaySettings[$paymentGateway->id]) && $gatewaySettings[$paymentGateway->id]['active'] === true;
        });

        if ($paymentGateways->isEmpty()) {
            throw OrderException::make('Подходящий платежный метод не найден для данных лимитов/валюты.');
        }

        $gateways = collect();

        $paymentGateways->each(function (PaymentGateway $gateway) use (&$gateways) {
            $commission = services()->commission()->getOrderServiceCommissionRate($gateway, $this->merchant);
dd($commission);
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
}
