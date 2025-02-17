<?php

namespace App\Services\Market\Utils\Parser;

use App\Enums\Market;
use App\Services\Market\Value\MarketPrices;
use App\Services\Money\Currency;

class Parser
{
    public function getPrices(Currency $currency, Market $market): MarketPrices
    {
        if ($market->equals(Market::GARANTEX) && $currency->equals(Currency::RUB())) {
            $prices = (new GrantexParser())->getPrices($currency);
        } elseif ($market->equals(Market::BYBIT)) {
            $prices = (new ByBitParser())->getPrices($currency);
        } else {
            throw new \Exception('Error: Market not found.');
        }

        return $prices;
    }
}
