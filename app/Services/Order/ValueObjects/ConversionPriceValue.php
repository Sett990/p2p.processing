<?php

namespace App\Services\Order\ValueObjects;

use App\Services\Money\Money;

readonly class ConversionPriceValue
{
    public function __construct(
        public Money $basePrice,
        public Money $actualPrice,
    )
    {}
}
