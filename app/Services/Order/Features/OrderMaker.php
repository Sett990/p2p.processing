<?php

namespace App\Services\Order\Features;

use App\DTO\Order\OrderCreateDTO;
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
        protected OrderCreateDTO $dto
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
            'external_id' => $this->dto->externalID,
            'merchant_id' => $this->dto->merchant->id,
            'base_amount' => $this->dto->amount,
            'amount' => $this->dto->amount,
            'profit' => Money::fromPrecision(0, Currency::USDT()),
            'trader_profit' => Money::fromPrecision(0, Currency::USDT()),
            'merchant_profit' => Money::fromPrecision(0, Currency::USDT()),
            'service_profit' => Money::fromPrecision(0, Currency::USDT()),
            'currency' => $this->dto->amount->getCurrency(),
            'base_conversion_price' => Money::fromPrecision(0, $this->dto->amount->getCurrency()),
            'conversion_price' => Money::fromPrecision(0, $this->dto->amount->getCurrency()),
            'trader_commission_rate' => 0,
            'service_commission_rate_total' => 0,
            'service_commission_rate_merchant' => 0,
            'service_commission_rate_client' => 0,
            'status' => OrderStatus::PENDING,
            'callback_url' => $this->dto->callbackURL,
            'success_url' => $this->dto->successURL,
            'fail_url' => $this->dto->failURL,
            'is_h2h' => $this->dto->h2h,
            'is_manually' => $this->dto->manually,
            'payment_gateway_id' => null,
            'payment_detail_id' => null,
            'expires_at' => null,
        ]);
    }

    protected function validate(): void
    {
        if (!$this->dto->merchant->validated_at) {
            throw new OrderException('Мерчант находится на модерации.');
        }
        if ($this->dto->merchant->banned_at) {
            throw new OrderException('Мерчант заблокирован.');
        }
        if (!$this->dto->merchant->active) {
            throw new OrderException('Мерчант отключен.');
        }
        if ($this->dto->h2h && $this->dto->successURL) {
            throw new OrderException('Для H2H сделок невозможно указать success url.');
        }
        if ($this->dto->h2h && $this->dto->failURL) {
            throw new OrderException('Для H2H сделок невозможно указать fail url.');
        }
        if ($this->dto->manually && $this->dto->h2h) {
            throw new OrderException('Сделка не может быть одновременно H2H и Manually.');
        }
    }
}
