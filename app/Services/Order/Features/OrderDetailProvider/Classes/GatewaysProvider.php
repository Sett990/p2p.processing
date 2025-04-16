<?php

namespace App\Services\Order\Features\OrderDetailProvider\Classes;

use App\Exceptions\OrderException;
use App\Models\Merchant;
use App\Models\PaymentGateway;
use App\Services\Money\Currency;
use App\Services\Money\Money;
use App\Services\Order\Features\OrderDetailProvider\Classes\Utils\GatewayFactory;
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
        $inactiveGatewayIds = collect($this->merchant->gateway_settings)
            ->filter(fn($settings) => isset($settings['active']) && $settings['active'] === false)
            ->keys()
            ->all();

        if ($this->gateway) {
            $paymentGateways = PaymentGateway::query()
                ->where(function ($query) {
                    $query->where('min_limit', '<=', intval($this->amount->toBeauty()));
                    $query->where('max_limit', '>=', intval($this->amount->toBeauty()));
                })
                ->where('code', $this->gateway->code)
                ->whereNotIn('id', $inactiveGatewayIds)
                ->active()
                ->get();
        } else if ($this->currency) {
            $paymentGateways = PaymentGateway::query()
                ->where(function ($query) {
                    $query->where('min_limit', '<=', intval($this->amount->toBeauty()));
                    $query->where('max_limit', '>=', intval($this->amount->toBeauty()));
                })
                ->where('currency', $this->currency->getCode())
                ->where('is_intrabank', false)
                ->whereNotIn('id', $inactiveGatewayIds)
                ->active()
                ->get();
        } else {
            throw OrderException::make('Требуется валюта или платежный метод.');
        }

        if ($paymentGateways->isEmpty()) {
            throw OrderException::make('Подходящий платежный метод не найден для данных лимитов/валюты.');
        }

        dd(1);

        $gateways = collect();

        $paymentGateways->each(function (PaymentGateway $paymentGateway) use (&$gateways) {
            $gateways->push(
                (new GatewayFactory($this->merchant))->make($paymentGateway)
            );
        });

        return $gateways;
    }
}
