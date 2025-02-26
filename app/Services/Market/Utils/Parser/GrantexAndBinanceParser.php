<?php

namespace App\Services\Market\Utils\Parser;

use App\Services\Market\Value\MarketPrices;
use App\Services\Money\Currency;
use App\Services\Money\Money;
use Exception;
use Illuminate\Support\Facades\Http;

class GrantexAndBinanceParser extends BaseParser
{
    public function getPrices(Currency $currency): MarketPrices
    {
        if ($currency->equals(Currency::RUB())) {
            list($buyPrice, $sellPrice) = $this->getGarantexPrices();
        } else {
            $buyPrice = $this->getBinancePrice($currency->getCode(), 'buy');
            $sellPrice = $this->getBinancePrice($currency->getCode(), 'sell');
        }

        return new MarketPrices(
            buyPrice: Money::fromPrecision($buyPrice, $currency),
            sellPrice: Money::fromPrecision($sellPrice, $currency),
        );
    }

    public function getGarantexPrices(): array
    {
        $market = 'usdtrub';
        $url = "https://garantex.org/api/v2/depth?market=$market";
        $response = Http::get($url);

        if ($response->failed()) {
            throw new Exception("Не удалось получить данные от Garantex API.");
        }

        $data = $response->json();

        if (empty($data['asks']) || empty($data['bids'])) {
            throw new Exception("Нет данных о стакане заявок.");
        }

        $topAsks = array_slice($data['asks'], 0, 5); //Продажа
        $topBids = array_slice($data['bids'], 0, 5); //Покупка

        $averageAskPrice = array_sum(array_column($topAsks, 'price')) / count($topAsks);
        $averageBidPrice = array_sum(array_column($topBids, 'price')) / count($topBids);

        return [$averageAskPrice, $averageBidPrice];
    }

    public function getBinancePrice(string $fiat, string $tradeType): ?float
    {
        $payload = [
            "page" => 1,
            "rows" => 5, // Запрашиваем последние 5 предложений
            "payTypes" => [],
            "asset" => "USDT",
            "tradeType" => strtoupper($tradeType),
            "fiat" => strtoupper($fiat),
            "publisherType" => null
        ];

        $headers = [
            "Content-Type" => "application/json",
            "User-Agent" => "Mozilla/5.0 (Windows NT 10.0; Win64; x64)"
        ];

        $response = Http::withHeaders($headers)->post('https://p2p.binance.com/bapi/c2c/v2/friendly/c2c/adv/search', $payload);
        $response->throw();

        if (!$response->successful()) {
            return null;
        }

        $data = $response->json();
        $prices = array_map(fn($offer) => (float) $offer['adv']['price'], $data['data'] ?? []);

        if (empty($prices)) {
            return null;
        }

        return array_sum($prices) / count($prices); // Среднее значение
    }
}
