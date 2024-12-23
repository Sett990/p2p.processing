<?php

namespace App\Services\Order\Utils;

use App\Models\PaymentGateway;
use Illuminate\Support\Carbon;

class TraderCommissionRate
{
    protected float $commissionRate;

    public function __construct(
        protected PaymentGateway $paymentGateway
    )
    {
        $this->commissionRate = $this->paymentGateway->buy_price_markup_rate;
    }

    public function getCommissionRate(): float
    {
        $primeTimeBonus = services()->settings()->getPrimeTimeBonus();
        $start = Carbon::createFromTimeString($primeTimeBonus->starts);
        $end = Carbon::createFromTimeString($primeTimeBonus->ends);

        if (now()->between($start, $end)) {
            return round($this->commissionRate + $primeTimeBonus->rate, 2);
        }

        return $this->commissionRate;
    }
}
