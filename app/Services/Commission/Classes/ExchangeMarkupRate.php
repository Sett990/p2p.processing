<?php

namespace App\Services\Commission\Classes;

use App\Models\PaymentGateway;
use Illuminate\Support\Carbon;

class ExchangeMarkupRate
{
    protected float $buyPriceMarkupRate;
    protected float $sellPriceMarkupRate;

    public function __construct(
        protected PaymentGateway $paymentGateway
    )
    {
        $this->buyPriceMarkupRate = $this->paymentGateway->buy_price_markup_rate;
        $this->sellPriceMarkupRate = $this->paymentGateway->sell_price_markup_rate;
    }

    public function getBuyPriceMarkupRate(): float
    {
        $primeTimeBonus = services()->settings()->getPrimeTimeBonus();
        $start = Carbon::createFromTimeString($primeTimeBonus->starts);
        $end = Carbon::createFromTimeString($primeTimeBonus->ends);

        if (now()->between($start, $end)) {
            return round($this->buyPriceMarkupRate + $primeTimeBonus->rate, 2);
        }

        return $this->buyPriceMarkupRate;
    }

    public function getSellPriceMarkupRate(): float
    {
        return $this->sellPriceMarkupRate;
    }
}
