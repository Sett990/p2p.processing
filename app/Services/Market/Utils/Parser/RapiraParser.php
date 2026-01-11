<?php

namespace App\Services\Market\Utils\Parser;

use App\Services\Market\Value\MarketPrices;
use App\Services\Money\Currency;
use App\Services\Money\Money;
use Exception;
use Illuminate\Support\Facades\Http;

class RapiraParser extends BaseParser
{
    public function getPrices(Currency $currency): MarketPrices
    {
        if (! $currency->equals(Currency::RUB())) {
            throw new Exception('Rapira market supports only RUB.');
        }

        [$buyPrice, $sellPrice] = $this->getRapiraPrices();

        return new MarketPrices(
            buyPrice: Money::fromPrecision($buyPrice, $currency),
            sellPrice: Money::fromPrecision($sellPrice, $currency),
        );
    }

    public function getRapiraPrices(): array
    {
        $url = "https://api.rapira.net/market/exchange-plate-mini?symbol=USDT/RUB";
        $response = Http::get($url);

        if ($response->failed()) {
            throw new Exception("Не удалось получить данные от Rapira API.");
        }

        $data = $response->json();

        if (empty($data['ask']['items']) || empty($data['bid']['items'])) {
            throw new Exception("Нет данных о стакане заявок.");
        }

        // Получаем первые 5 заявок на продажу и покупку
        $topAsks = array_slice($data['ask']['items'], 0, 5); // Продажа USDT
        $topBids = array_slice($data['bid']['items'], 0, 5); // Покупка USDT

        // Вычисляем среднюю цену
        $averageAskPrice = array_sum(array_column($topAsks, 'price')) / count($topAsks);
        $averageBidPrice = array_sum(array_column($topBids, 'price')) / count($topBids);

        return [$averageAskPrice, $averageBidPrice];
    }
}
