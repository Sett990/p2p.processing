<?php

namespace App\Services\Settings;

use App\Contracts\SettingsServiceContract;
use App\Exceptions\SettingsException;
use App\Models\Setting;
use App\Models\ValueObjects\Settings\CurrencyPriceParserSettings;
use App\Models\ValueObjects\Settings\PrimeTimeSettings;
use App\Services\Money\Currency;

class SettingsService implements SettingsServiceContract
{
    const PRIME_TIME_BONUS_STARTS = 'prime_time_bonus_starts';
    const PRIME_TIME_BONUS_ENDS = 'prime_time_bonus_ends';
    const PRIME_TIME_BONUS_RATE = 'prime_time_bonus_rate';
    const CURRENCY_PRICE_PARSER_SETTINGS = 'currency_price_parser_settings';
    const SUPPORT_LINK = 'support_link';

    public function getPrimeTimeBonus(): PrimeTimeSettings
    {
        return new PrimeTimeSettings(
            starts: $this->getParam(self::PRIME_TIME_BONUS_STARTS),
            ends: $this->getParam(self::PRIME_TIME_BONUS_ENDS),
            rate: $this->getParam(self::PRIME_TIME_BONUS_RATE)
        );
    }

    public function updatePrimeTimeBonus(string $starts, string $ends, float $rate): void
    {
        $this->updateParam(self::PRIME_TIME_BONUS_STARTS, $starts);
        $this->updateParam(self::PRIME_TIME_BONUS_ENDS, $ends);
        $this->updateParam(self::PRIME_TIME_BONUS_RATE, round($rate, 2));
    }

    public function getCurrencyPriceParser(Currency $currency): CurrencyPriceParserSettings
    {
        $param = json_decode($this->getParam(self::CURRENCY_PRICE_PARSER_SETTINGS), true);

        return new CurrencyPriceParserSettings(...$param[$currency->getCode()]);
    }

    public function updateCurrencyPriceParser(Currency $currency, CurrencyPriceParserSettings $settings): void
    {
        $param = json_decode($this->getParam(self::CURRENCY_PRICE_PARSER_SETTINGS), true);

        $param[$currency->getCode()] = $settings->toArray();

        $this->updateParam(self::CURRENCY_PRICE_PARSER_SETTINGS, $param);
    }

    public function getSupportLink(): ?string
    {
        return $this->getParam(self::SUPPORT_LINK);
    }

    public function updateSupportLink(string $link): void
    {
        $this->updateParam(self::SUPPORT_LINK, $link);
    }

    public function createAll(): void
    {
        Setting::firstOrCreate([
            'key' => self::PRIME_TIME_BONUS_STARTS,
            'value' => '00:00',
        ]);
        Setting::firstOrCreate([
            'key' => self::PRIME_TIME_BONUS_ENDS,
            'value' => '07:00',
        ]);
        Setting::firstOrCreate([
            'key' => self::PRIME_TIME_BONUS_RATE,
            'value' => '1.2',
        ]);
        Setting::firstOrCreate([
            'key' => self::SUPPORT_LINK,
            'value' => null,
        ]);

        $currencies = [];
        Currency::getAll()->each(function (Currency $currency) use (&$currencies) {
            $currencies[$currency->getCode()] = (new CurrencyPriceParserSettings(...[
                'amount' => null,
                'payment_method' => null,
                'ad_quantity' => 3,
            ]))->toArray();
        });
        Setting::updateOrCreate(['key' => self::CURRENCY_PRICE_PARSER_SETTINGS], [
            'key' => self::CURRENCY_PRICE_PARSER_SETTINGS,
            'value' => json_encode($currencies),
        ]);
    }

    protected function getParam(string $key): mixed
    {
        return Setting::where('key', $key)->first()->value;
    }

    protected function updateParam(string $key, mixed $value): bool
    {
        return Setting::where('key', $key)->update(['value' => $value]);
    }
}
