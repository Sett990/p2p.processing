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
            $paymentGateways = PaymentGateway::query()
                ->where(function ($query) {
                    $query->where('min_limit', '<=', intval($this->amount->toBeauty()));
                    $query->where('max_limit', '>=', intval($this->amount->toBeauty()));
                })
                ->whereIn('code', [$this->gateway->code])
                ->active()
                ->get();
        } else if ($this->currency) {
            $paymentGateways = PaymentGateway::query()
                ->where(function ($query) {
                    $query->where('min_limit', '<=', intval($this->amount->toBeauty()));
                    $query->where('max_limit', '>=', intval($this->amount->toBeauty()));
                })
                ->where('currency', $this->currency->getCode())
                ->active()
                ->get();
        } else {
            throw OrderException::make('Требуется валюта или платежный метод.');
        }

        if ($paymentGateways->isEmpty()) {
            throw OrderException::make('Подходящий платежный метод не найден для данных лимитов/валюты.');
        }

        $gatewaySettings = $this->merchant->gateway_settings;

        $paymentGateways = $paymentGateways->filter(function (PaymentGateway $paymentGateway) use ($gatewaySettings) {
            return $gatewaySettings[$paymentGateway->id]['active'] ?? true;
        });

        if ($paymentGateways->isEmpty()) {
            throw OrderException::make('Подходящий платежный метод не найден для данных лимитов/валюты.');
        }

        $gateways = collect();

        $paymentGateways->each(function (PaymentGateway $paymentGateway) use (&$gateways) {
            $customGatewaySettings = null;
            if (! empty($this->merchant->gateway_settings[$paymentGateway->id])) {
                $customGatewaySettings = $this->merchant->gateway_settings[$paymentGateway->id];
            }

            $serviceCommissionRateTotal = $paymentGateway->total_service_commission_rate_for_orders;

            if (isset($customGatewaySettings['custom_gateway_commission']) && $customGatewaySettings['custom_gateway_commission'] > 0) {
                $serviceCommissionRateTotal = $customGatewaySettings['custom_gateway_commission'];
            } else if (isset($customGatewaySettings['custom_gateway_commission']) && (int)$customGatewaySettings['custom_gateway_commission'] === 0) {
                $serviceCommissionRateTotal = 0;
            }

            if (! empty($customGatewaySettings['custom_gateway_reservation_time'])) {
                $reservationTime = (int)$customGatewaySettings['custom_gateway_reservation_time'];
            } else {
                $reservationTime = $paymentGateway->reservation_time_for_orders;
            }

            $gateways->push(
                new Gateway(
                    id: $paymentGateway->id,
                    code: $paymentGateway->code,
                    reservationTime: $reservationTime,
                    serviceCommissionRate: $serviceCommissionRateTotal,
                    traderCommissionRate: $paymentGateway->trader_commission_rate_for_orders,
                    isSBP: $paymentGateway->is_sbp
                )
            );
        });

        return $gateways;
    }
}
