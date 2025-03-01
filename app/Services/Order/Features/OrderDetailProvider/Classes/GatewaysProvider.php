<?php

namespace App\Services\Order\Features\OrderDetailProvider\Classes;

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
    ){

    }

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

        $paymentGateways->each(function (PaymentGateway $paymentGateway) use (&$gateways) {
            $gatewaySettings = $this->merchant->gateway_settings;

            $serviceCommissionRateTotal = $paymentGateway->order_service_commission_rate;

            if (isset($gatewaySettings[$paymentGateway->id]['custom_gateway_commission']) && $gatewaySettings[$paymentGateway->id]['custom_gateway_commission'] > 0) {
                $serviceCommissionRateTotal = $gatewaySettings[$paymentGateway->id]['custom_gateway_commission'];
            } else if (isset($gatewaySettings[$paymentGateway->id]['custom_gateway_commission']) && (int)$gatewaySettings[$paymentGateway->id]['custom_gateway_commission'] === 0) {
                $serviceCommissionRateTotal = 0;
            }

            $customGatewaySettings = null;
            if (! empty($this->merchant->gateway_settings[$paymentGateway->id])) {
                $customGatewaySettings = $this->merchant->gateway_settings[$paymentGateway->id];
            }

            if (! empty($customGatewaySettings['custom_gateway_reservation_time'])) {
                $reservationTime = (int)$customGatewaySettings['custom_gateway_reservation_time'];
            } else {
                $reservationTime = $paymentGateway->reservation_time;
            }

            $gateways->push(
                new Gateway(
                    id: $paymentGateway->id,
                    code: $paymentGateway->code,
                    reservationTime: $reservationTime,
                    serviceCommissionRate: $serviceCommissionRateTotal,
                    traderCommissionRate: $paymentGateway->buy_price_markup_rate,
                    isSBP: $paymentGateway->is_sbp
                )
            );
        });

        return $gateways;
    }
}
