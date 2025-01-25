<?php

namespace App\Services\Order\Features\OrderDetailProvider\Values;

use App\Services\Money\Money;

class Gateway
{
    public function __construct(
        public int $id,
        public string $code,
        public int $reservationTime,
        public Money $amountWithServiceCommission,
        public float $traderMarkupRate,
        public float $serviceCommissionRateTotal,
        public float $serviceCommissionRateMerchant,
        public float $serviceCommissionRateClient,
        public bool $isSBP,
    )
    {}
}
