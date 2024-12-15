<?php

namespace App\Services\Wallet\ValueObjects;

use App\Services\Money\Money;

class EscrowBalance extends ValueObject
{
    public function __construct(
        public Money $balance,
        public int $count,
    )
    {}
}
