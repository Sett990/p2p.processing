<?php

namespace App\Contracts;

use App\Services\Money\Currency;
use App\Services\Money\Money;

interface MarketServiceContract
{
    public function loadAllPrices(): void;

    public function loadPricesFor(Currency $currency): void;

    public function getSellPrice(Currency $currency): Money;

    public function getBuyPrice(Currency $currency): Money;

    public function loadPaymentMethodsList(): void;

    public function getPaymentMethods(Currency $currency): array;
}
