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
    const FUNDS_ON_HOLD_TIME = 'funds_on_hold_time';
    const MAX_PENDING_DISPUTES = 'max_pending_disputes';
    const MAX_REJECTED_DISPUTES = 'max_rejected_disputes';
    const TEMP_VIP_REQUIRED_DEALS = 'temp_vip_required_deals';
    const TEMP_VIP_DURATION_MINUTES = 'temp_vip_duration_minutes';
    const TEMP_VIP_ENABLED = 'temp_vip_enabled';
    const DEFAULT_RESERVE_BALANCE_LIMIT = 'default_reserve_balance_limit';

    protected $settings = null;

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
        $settings = $param[$currency->getCode()] ?? null;

        return CurrencyPriceParserSettings::fromArray($settings);
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

    public function getFundsOnHoldTime(): int
    {
        return $this->getParam(self::FUNDS_ON_HOLD_TIME);
    }

    public function updateFundsOnHoldTime(int $minutes): void
    {
        $this->updateParam(self::FUNDS_ON_HOLD_TIME, $minutes);
    }

    public function getMaxPendingDisputes(): int
    {
        return $this->getParam(self::MAX_PENDING_DISPUTES);
    }

    public function updateMaxPendingDisputes(int $value): void
    {
        $this->updateParam(self::MAX_PENDING_DISPUTES, $value);
    }

    public function getMaxRejectedDisputes(): array
    {
        $value = $this->getParam(self::MAX_REJECTED_DISPUTES);
        if (!$value) {
            return ['count' => 0, 'period' => 0];
        }
        return json_decode($value, true);
    }

    public function updateMaxRejectedDisputes(int $count, int $period): void
    {
        $this->updateParam(self::MAX_REJECTED_DISPUTES, json_encode(['count' => $count, 'period' => $period]));
    }

    public function getTempVipRequiredDeals(): int
    {
        return (int) $this->getParam(self::TEMP_VIP_REQUIRED_DEALS);
    }

    public function updateTempVipRequiredDeals(int $value): void
    {
        $this->updateParam(self::TEMP_VIP_REQUIRED_DEALS, $value);
    }

    public function getTempVipDurationMinutes(): int
    {
        return (int) $this->getParam(self::TEMP_VIP_DURATION_MINUTES);
    }

    public function updateTempVipDurationMinutes(int $value): void
    {
        $this->updateParam(self::TEMP_VIP_DURATION_MINUTES, $value);
    }

    public function isTempVipEnabled(): bool
    {
        return (bool) (int) $this->getParam(self::TEMP_VIP_ENABLED);
    }

    public function updateTempVipEnabled(bool $enabled): void
    {
        $this->updateParam(self::TEMP_VIP_ENABLED, $enabled ? 1 : 0);

        if (! $enabled) {
            // Полное выключение функционала: сбрасываем временный VIP и прогресс для всех non-VIP пользователей,
            // и отключаем VIP-лимиты на реквизитах (перманентный VIP при этом не затрагиваем).
            $now = now();

            \App\Models\User::query()
                ->where('is_vip', false)
                ->update([
                    'temp_vip_active_until' => null,
                    'temp_vip_can_activate' => false,
                    'temp_vip_progress_start_at' => $now,
                ]);

            \App\Models\PaymentDetail::query()
                ->whereIn('user_id', \App\Models\User::query()->where('is_vip', false)->select('id'))
                ->update([
                    'min_order_amount' => null,
                    'max_order_amount' => null,
                ]);
        }
    }

    public function getDefaultReserveBalanceLimit(): int
    {
        return (int) $this->getParam(self::DEFAULT_RESERVE_BALANCE_LIMIT);
    }

    public function updateDefaultReserveBalanceLimit(int $value): void
    {
        $this->updateParam(self::DEFAULT_RESERVE_BALANCE_LIMIT, $value);
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
        Setting::firstOrCreate([
            'key' => self::FUNDS_ON_HOLD_TIME,
            'value' => 1440,
        ]);

        Setting::firstOrCreate([
            'key' => self::MAX_PENDING_DISPUTES,
            'value' => 5,
        ]);

        Setting::firstOrCreate([
            'key' => self::MAX_REJECTED_DISPUTES,
            'value' => json_encode(['count' => 10, 'period' => 60]),
        ]);

        Setting::firstOrCreate([
            'key' => self::TEMP_VIP_REQUIRED_DEALS,
            'value' => 30,
        ]);

        Setting::firstOrCreate([
            'key' => self::TEMP_VIP_DURATION_MINUTES,
            'value' => 120,
        ]);

        Setting::firstOrCreate([
            'key' => self::TEMP_VIP_ENABLED,
            'value' => 1,
        ]);

        Setting::firstOrCreate([
            'key' => self::DEFAULT_RESERVE_BALANCE_LIMIT,
            'value' => 500,
        ]);

        $currenciesJson = $this->getParam(self::CURRENCY_PRICE_PARSER_SETTINGS);
        if (! empty($currenciesJson)) {
            $currencies = json_decode($currenciesJson, true);
        } else {
            $currencies = [];
        }

        Currency::getAll()->each(function (Currency $currency) use (&$currencies) {
            if (empty($currencies[$currency->getCode()])) {
                $currencies[$currency->getCode()] = (new CurrencyPriceParserSettings(
                    amount: null,
                    payment_methods: [],
                    ad_quantity: 50,
                    min_recent_orders: 100,
                ))->toArray();
            }
        });

        Setting::updateOrCreate(['key' => self::CURRENCY_PRICE_PARSER_SETTINGS], [
            'key' => self::CURRENCY_PRICE_PARSER_SETTINGS,
            'value' => json_encode($currencies),
        ]);

        cache()->put('app-settings', Setting::all());
    }

    protected function getParam(string $key): mixed
    {
        if (! $this->settings) {
            $settings = cache()->get('app-settings');

            if (! $settings) {
                $settings = cache()->rememberForever('app-settings', function () {
                    return Setting::all();
                });
            }

            $this->settings = $settings;
        }

        return $this->settings->where('key', $key)->first()->value;
    }

    protected function updateParam(string $key, mixed $value): bool
    {
        $res = Setting::where('key', $key)->update(['value' => $value]);

        cache()->put('app-settings', Setting::all());
        $this->settings = null;

        return (bool)$res;
    }
}
