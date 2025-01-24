<?php

namespace App\Services\Order\OrderDetails\Values;

use App\Services\Money\Money;

class Trader
{
    public function __construct(
        public int $id,
        public Money $trustBalance,
        public array $exchangeMarkupRates
    )
    {}
}
