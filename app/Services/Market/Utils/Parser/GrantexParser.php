<?php

namespace App\Services\Market\Utils\Parser;

use App\Services\Market\Value\MarketPrices;
use App\Services\Money\Currency;
use App\Services\Money\Money;
use Exception;
use Illuminate\Support\Facades\Http;

class GrantexParser extends BaseParser
{
    public function getPrices(Currency $currency): MarketPrices
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


        return new MarketPrices(
            buyPrice: Money::fromPrecision($averageAskPrice, $currency),
            sellPrice: Money::fromPrecision($averageBidPrice, $currency),
        );
    }
}
