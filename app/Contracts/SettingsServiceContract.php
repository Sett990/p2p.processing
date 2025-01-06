<?php

namespace App\Contracts;

use App\Models\ValueObjects\Settings\CurrencyPriceParserSettings;
use App\Models\ValueObjects\Settings\PrimeTimeSettings;
use App\Services\Money\Currency;

interface SettingsServiceContract
{
    public function getPrimeTimeBonus(): PrimeTimeSettings;

    public function updatePrimeTimeBonus(string $starts, string $ends, float $rate): void;

    public function getCurrencyPriceParser(Currency $currency): CurrencyPriceParserSettings;

    public function updateCurrencyPriceParser(Currency $currency, CurrencyPriceParserSettings $settings): void;

    public function getSupportLink(): ?string;

    public function getFundsOnHoldTime(): int;

    public function updateFundsOnHoldTime(int $minutes);

    public function updateSupportLink(string $link): void;

    public function createAll(): void;
}
