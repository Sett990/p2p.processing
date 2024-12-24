<?php

namespace App\Services\Order\Utils;

use App\Services\Commission\ValueObjects\OrderServiceCommissionValue;
use App\Services\Money\Money;

class PaymentAmountCalculator
{
    public function __construct(
        protected Money $amount,
        protected OrderServiceCommissionValue $serviceCommission,
    )
    {}

    public function calculate(): Money
    {
        $client_commission_amount = 0;
        if ($this->serviceCommission->client > 0) {
            $client_commission_amount = $this->amount
                ->mul($this->serviceCommission->client / 100);
            $client_commission_amount = round($client_commission_amount->toBeauty());
        }

        return $this->amount->add($client_commission_amount);
    }
}
