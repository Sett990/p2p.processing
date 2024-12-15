<?php

namespace App\Services\Wallet\ValueObjects;

class EscrowBalances extends ValueObject
{
    public function __construct(
        public EscrowBalance $orders,
        public EscrowBalance $disputes,
    )
    {}
}
