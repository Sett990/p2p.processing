<?php

namespace App\Services\Market\Utils;

use App\Enums\Market;
use App\Services\Money\Currency;

class MarketStore
{
    protected static function cacheKey(Currency $currency, Market $market): string
    {
        return 'conversion-price-for-' . $currency->getCode() . '-' . $market->value;
    }

    public static function putPrice(Currency $currency, Market $market, string $buy_price, string $sell_price): void
    {
        $time = is_local() ? 60 * 60 * 24 * 365 : 60 * 30;

        cache()->put(self::cacheKey($currency, $market), [
            'buy_price' => $buy_price,
            'sell_price' => $sell_price,
        ], $time);
    }

    public static function getBuyPrice(Currency $currency, Market $market): ?string
    {
        $prices = cache()->get(self::cacheKey($currency, $market));

        if (empty($prices)) {
            return null;
        }

        return $prices['buy_price'];
    }

    public static function getSellPrice(Currency $currency, Market $market): ?string
    {
        $prices = cache()->get(self::cacheKey($currency, $market));

        if (empty($prices)) {
            return null;
        }

        return $prices['sell_price'];
    }

    public static function putPaymentMethodsList(array $paymentMethods): void
    {
        cache()
            ->put(
                key: 'currencies.price-parsers.methods-list',
                value: $paymentMethods,
                ttl: 60 * 60 * 24
            );
    }

    public static function getPaymentMethodsList(): array
    {
        return cache()->get('currencies.price-parsers.methods-list');
    }
}
