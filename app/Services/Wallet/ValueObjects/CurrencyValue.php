<?php

namespace App\Services\Wallet\ValueObjects;

use App\Services\Money\Currency;

class CurrencyValue extends ValueObject
{
    public function __construct(
        public Currency $primaryCurrency,
        public Currency $secondaryCurrency
    )
    {}
}
