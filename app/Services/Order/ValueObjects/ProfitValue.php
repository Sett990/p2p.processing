<?php

namespace App\Services\Order\ValueObjects;

use App\Services\Money\Money;

readonly class ProfitValue
{
    public function __construct(
        public Money $profit,
        public Money $traderProfit,
        public Money $serviceProfit,
        public Money $merchantProfit,
    )
    {}
}
