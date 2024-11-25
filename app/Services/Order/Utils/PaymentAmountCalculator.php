<?php

namespace App\Services\Order\Utils;

use App\Services\Money\Money;
use App\Services\Order\ValueObjects\ServiceCommissionValue;

class PaymentAmountCalculator
{
    public function __construct(
        protected Money $amount,
        protected ServiceCommissionValue $serviceCommission,
    )
    {}

    public function calculate(): Money
    {
        $client_commission_amount = 0;
        if ($this->serviceCommission->serviceCommissionRateClient > 0) {
            $client_commission_amount = $this->amount
                ->mul($this->serviceCommission->serviceCommissionRateClient / 100);
        }

        return $this->amount->add($client_commission_amount);
    }
}
