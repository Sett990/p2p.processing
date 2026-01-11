<?php

namespace App\Services\Market\Utils\Parser;

use App\Enums\MarketEnum;
use App\Services\Market\Value\MarketPrices;
use App\Services\Money\Currency;

class Parser
{
    public function getPrices(Currency $currency, MarketEnum $market): MarketPrices
    {
        if ($market->equals(MarketEnum::RAPIRA)) {
            $prices = (new RapiraParser())->getPrices($currency);
        } elseif ($market->equals(MarketEnum::BYBIT)) {
            $prices = (new ByBitParser())->getPrices($currency);
        } else {
            throw new \Exception('Error: Market not found.');
        }

        return $prices;
    }
}
