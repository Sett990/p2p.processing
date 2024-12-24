<?php

namespace App\Services\Commission\ValueObjects;

readonly class OrderServiceCommissionValue
{
    public function __construct(
        public float $total,
        public float $merchant,
        public float $client,
    )
    {}
}
