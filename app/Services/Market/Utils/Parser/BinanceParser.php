<?php

declare(strict_types=1);

namespace App\Services\Market\Utils\Parser;

use App\Services\Market\Value\MarketPrices;
use App\Services\Money\Currency;
use App\Services\Money\Money;
use Exception;
use Illuminate\Support\Facades\Http;

class BinanceParser extends BaseParser
{
    public function getPrices(Currency $currency): MarketPrices
    {
        if ($currency->equals(Currency::RUB())) {
            throw new Exception('Binance market supports all currencies except RUB.');
        }

        $buyPrice = $this->getBinancePrice($currency->getCode(), 'buy');
        $sellPrice = $this->getBinancePrice($currency->getCode(), 'sell');

        if ($buyPrice === null || $sellPrice === null) {
            throw new Exception('Не удалось получить данные от Binance API.');
        }

        return new MarketPrices(
            buyPrice: Money::fromPrecision((string) $buyPrice, $currency->getCode()),
            sellPrice: Money::fromPrecision((string) $sellPrice, $currency->getCode()),
        );
    }

    protected function getBinancePrice(string $fiat, string $tradeType): ?float
    {
        $payload = [
            "page" => 1,
            "rows" => 5,
            "payTypes" => [],
            "asset" => "USDT",
            "tradeType" => strtoupper($tradeType),
            "fiat" => strtoupper($fiat),
            "publisherType" => null,
        ];

        $headers = [
            "Content-Type" => "application/json",
            "User-Agent" => "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36",
        ];

        $response = Http::withHeaders($headers)
            ->post('https://p2p.binance.com/bapi/c2c/v2/friendly/c2c/adv/search', $payload);

        if (! $response->successful()) {
            throw new Exception('Binance API error: ' . $response->body());
        }

        $data = $response->json();

        $prices = array_map(
            fn ($offer) => (float) ($offer['adv']['price'] ?? 0),
            $data['data'] ?? []
        );
        $prices = array_filter($prices, fn (float $price) => $price > 0);

        if (empty($prices)) {
            return null;
        }

        return array_sum($prices) / count($prices);
    }
}
