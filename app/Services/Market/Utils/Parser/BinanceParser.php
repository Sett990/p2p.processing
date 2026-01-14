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

        $buyPrice = $this->getBinancePrice($currency->getCode(), 'sell');
        $sellPrice = $this->getBinancePrice($currency->getCode(), 'buy');

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

        $prices = [];
        foreach (($data['data'] ?? []) as $offer) {
            // Берём только "чистые" объявления без привилегий.
            // Если хотя бы одно поле не null — объявление отфильтровываем.
            if (($offer['privilegeDesc'] ?? null) !== null) {
                continue;
            }
            if (($offer['privilegeType'] ?? null) !== null) {
                continue;
            }
            if (($offer['privilegeTypeAdTotalCount'] ?? null) !== null) {
                continue;
            }

            $price = (float) ($offer['adv']['price'] ?? 0);
            if ($price <= 0) {
                continue;
            }

            $prices[] = $price;
        }

        if (empty($prices)) {
            return null;
        }

        return array_sum($prices) / count($prices);
    }
}
