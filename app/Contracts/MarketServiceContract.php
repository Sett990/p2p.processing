<?php

namespace App\Contracts;

use App\Enums\Market;
use App\Services\Money\Currency;
use App\Services\Money\Money;

interface MarketServiceContract
{
    public function loadAllPrices(): void;

    public function loadPricesFor(Currency $currency, Market $market): void;

    public function getSellPrice(Currency $currency, Market $market): Money;

    public function getBuyPrice(Currency $currency, Market $market): Money;

    public function loadPaymentMethodsList(): void;

    public function getPaymentMethods(Currency $currency): array;
}
