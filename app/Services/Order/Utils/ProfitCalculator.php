<?php

namespace App\Services\Order\Utils;

use App\Services\Money\Currency;
use App\Services\Money\Money;
use App\Services\Order\ValueObjects\ConversionPriceValue;
use App\Services\Order\ValueObjects\ProfitValue;
use App\Services\Order\ValueObjects\ServiceCommissionValue;

class ProfitCalculator
{
    public function __construct(
        protected Money $amount,
        protected ConversionPriceValue $conversionPrice,
        protected ServiceCommissionValue $serviceCommission
    )
    {}

    public function calculate()
    {
        $profit = $this->amount->convert($this->conversionPrice->actualPrice, Currency::USDT());
        $traderProfit = $this->amount
            ->convert($this->conversionPrice->basePrice, Currency::USDT())
            ->sub($profit);
        $serviceProfit = $profit->mul($this->serviceCommission->serviceCommissionRateTotal / 100);
        $merchantProfit = $profit->sub($serviceProfit);

        return new ProfitValue(
            profit: $profit,
            traderProfit: $traderProfit,
            serviceProfit: $serviceProfit,
            merchantProfit: $merchantProfit
        );
    }
}
