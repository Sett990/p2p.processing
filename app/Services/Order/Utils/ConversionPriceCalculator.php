<?php

namespace App\Services\Order\Utils;

use App\Services\Money\Currency;
use App\Services\Order\ValueObjects\ConversionPriceValue;

class ConversionPriceCalculator
{
    public function __construct(
        protected Currency $currency,
        protected float $traderCommissionRate,
    )
    {}

    public function calculate(): ConversionPriceValue
    {
        $basePrice = services()->market()->getBuyPrice($this->currency);
        $commissionPart = $basePrice->mul($this->traderCommissionRate / 100);
        $actualPrice = $basePrice->add($commissionPart);

        return new ConversionPriceValue(
            basePrice: $basePrice,
            actualPrice: $actualPrice,
        );
    }
}
