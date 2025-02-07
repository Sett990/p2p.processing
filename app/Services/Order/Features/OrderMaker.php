<?php

namespace App\Services\Order\Features;

use App\DTO\Order\CreateOrderDTO;
use App\Enums\OrderStatus;
use App\Exceptions\OrderException;
use App\Models\Order;
use App\Services\Money\Currency;
use App\Services\Money\Money;
use Illuminate\Support\Str;

class OrderMaker
{
    /**
     * @throws OrderException
     */
    public function __construct(
        protected CreateOrderDTO $data
    )
    {
        $this->validate();
    }

    /**
     * @throws OrderException
     */
    public function create(): Order
    {
        return Order::create([
            'uuid' => (string)Str::uuid(),
            'external_id' => $this->data->externalID,
            'merchant_id' => $this->data->merchant->id,
            'base_amount' => $this->data->amount,
            'amount' => $this->data->amount,
            'profit' => Money::fromPrecision(0, Currency::USDT()),
            'trader_profit' => Money::fromPrecision(0, Currency::USDT()),
            'merchant_profit' => Money::fromPrecision(0, Currency::USDT()),
            'service_profit' => Money::fromPrecision(0, Currency::USDT()),
            'currency' => $this->data->amount->getCurrency(),
            'base_conversion_price' => Money::fromPrecision(0, $this->data->amount->getCurrency()),
            'conversion_price' => Money::fromPrecision(0, $this->data->amount->getCurrency()),
            'trader_commission_rate' => 0,
            'service_commission_rate_total' => 0,
            'service_commission_rate_merchant' => 0,
            'service_commission_rate_client' => 0,
            'status' => OrderStatus::PENDING,
            'callback_url' => $this->data->callbackURL,
            'success_url' => $this->data->successURL,
            'fail_url' => $this->data->failURL,
            'is_h2h' => $this->data->h2h,
            'payment_gateway_id' => null,
            'payment_detail_id' => null,
            'expires_at' => null,
        ]);
    }

    protected function validate(): void
    {
        if (!$this->data->merchant->validated_at) {
            throw OrderException::merchantIsUnderModeration();
        }
        if ($this->data->merchant->banned_at) {
            throw OrderException::merchantBlocked();
        }
        if (!$this->data->merchant->active) {
            throw OrderException::merchantDisabled();
        }
        if ($this->data->h2h && $this->data->successURL) {
            throw OrderException::noSuccessUrlForH2HOrders();
        }
        if ($this->data->h2h && $this->data->failURL) {
            throw OrderException::noFailUrlForH2HOrders();
        }
        if ($this->data->manually && $this->data->h2h) {
            throw OrderException::noH2HAndManually();
        }
    }
}
