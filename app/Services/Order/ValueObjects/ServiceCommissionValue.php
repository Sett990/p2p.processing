<?php

namespace App\Services\Order\ValueObjects;

readonly class ServiceCommissionValue
{
    public function __construct(
        public float $serviceCommissionRateTotal,
        public float $serviceCommissionRateMerchant,
        public float $serviceCommissionRateClient,
    )
    {}
}
