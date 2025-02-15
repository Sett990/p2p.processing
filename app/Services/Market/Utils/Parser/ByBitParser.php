<?php

namespace App\Services\Market\Utils\Parser;

use App\Services\Market\Value\MarketPrices;
use App\Services\Money\Currency;
use App\Services\Money\Money;
use Illuminate\Support\Facades\Http;

class ByBitParser extends BaseParser
{
    public function getPrices(Currency $currency): MarketPrices
    {
        return new MarketPrices(
            $this->parseBuyPrice($currency),
            $this->parseSellPrice($currency),
        );
    }

    protected function parseBuyPrice(Currency $currency): Money
    {
        $price = $this->parseAveragePrice($currency);

        return Money::fromPrecision($price, $currency);
    }

    protected function parseSellPrice(Currency $currency): Money
    {
        $price = $this->parseAveragePrice($currency, false);

        return Money::fromPrecision($price, $currency);
    }

    public function parsePaymentMethodsList(): array
    {
        $result = Http::withHeaders([
            "accept" => "application/json",
            "accept-language" => "en",
            "cache-control" => "no-cache",
            "content-type" => "application/x-www-form-urlencoded",
            "lang" => "en",
            "platform" => "PC",
            "pragma" => "no-cache",
            "priority" => "u=1, i",
            "sec-ch-ua-mobile" => "?0",
            'User-Agent' => 'PostmanRuntime/7.42.0',
            'Accept-Encoding' => 'gzip, deflate, br',
        ])
            ->post('https://api2.bybit.com/fiat/otc/configuration/queryAllPaymentList')
            ->json();

        if ($result['ret_msg'] !== 'SUCCESS') {
            throw new \Exception('Error: ' . $result['ext_info']);
        }

        if (empty($result['result'])) {
            throw new \Exception('Empty result');
        }

        return $result['result'];
    }

    protected function parseAveragePrice(Currency $currency, bool $buy = true): float
    {
        $settings = services()->settings()->getCurrencyPriceParser($currency);

        $ad_quantity = $settings->ad_quantity ?: 3;

        $data = [
            'userId' => "",
            'tokenId' => "USDT",
            'currencyId' => strtoupper($currency->getCode()),
            'payment' => $settings->payment_method ? [strval($settings->payment_method)] : [],
            'side' => strval(intval($buy)), //buy = 1, sell = 0
            'size' => strval($ad_quantity),
            'page' => "1",
            'amount' => $settings->amount ? strval($settings->amount) : "",
            'authMaker' => false,
            'canTrade' => false
        ];

        $result = Http::asJson()
            ->withHeaders([
                'Accept' => '*/*',
                'Accept-Encoding' => 'gzip, deflate, br',
            ])
            ->post('https://api2.bybit.com/fiat/otc/item/online', $data);

        $items = $result->json()['result']['items'];

        $prices = [];
        foreach ($items as $item) {
            $prices[] = (float)$item['price'];
        }

        $delimiter = min(count($prices), $ad_quantity);

        return round(array_sum($prices) / $delimiter, 2);
    }
}
