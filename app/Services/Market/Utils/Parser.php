<?php

namespace App\Services\Market\Utils;

use App\Services\Money\Currency;
use App\Services\Money\Money;
use Illuminate\Support\Facades\Http;

class Parser
{
    public function parseBuyPrice(Currency $currency): Money
    {
        $price = $this->parseAveragePrice($currency);

        return Money::fromPrecision($price, $currency);
    }

    public function parseSellPrice(Currency $currency): Money
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
                'User-Agent' => 'PostmanRuntime/7.42.0',
                'Accept' => '*/*',
                'Accept-Encoding' => 'gzip, deflate, br',
                'Cookie' => '_abck=FC47212981BBD039DCD3C2E5A40986D5~-1~YAAQ+9MXApknJjCTAQAArGYoZAx8HYAndQYfnk20lvIYdhrkDX8noi2iPZiLil14fgPtSVBQeA14KRqGwO9s2ZU6dBxFVuf9k3znQdgut18aWfP36Lzrq9ZSzEX2oJf9einWlV5P5DtYyg0zTG9iIQ+dIMLGZpL2OtfSyt39e529Jlmvl2/uxpkYfkm91WKqfKUOxpxtl9vm3NU73R6CNt9ux0zd0xGNqZ5q8p8Kc0AhO8nAWiLRMKoOoGDJObxM2mJ+hrWe8EQ9IM/PJlhjItEfb1iBaCT5bJWsCqDsUwCHvdqbi2BRgAPQLallN/uI5iw4XaT0O5/roapO8BT53/1HLDOXvXYn/vktRVoawpGv2CWK0bpIK8CLnx/eAfCoh30xNFOX~-1~-1~-1; ak_bmsc=4BE8321FE1CC273497B390AA72C25162~000000000000000000000000000000~YAAQ+9MXAsuMJjCTAQAAMDEtZBn2XeS2YDnTeEslMZ5Q03qzgdILYXFfr2+8LL35x+BJAmJ6bLD5DuJe28fQfk3gf8Jb4O1v/jCDkWY9fkwBlRFkJTghCuUjQeuVJhT3DWx6g7P5l8tGzbR3MRKJI3hivzXypIXfjdxY5ucZYIBGp4BdNaNHK9b6EmA2ac1yd6/+ShU27858K4sU5a0F+XKQm2B4PTXry9T2wN8cqRZoZ8qVnYeue2Wxb6ectB/8iSjSrzZQa/ekT3aTdRk1roVuglVRLqD4Q7m0vIpmPUDktyJTLFTCDD4iFg2KcWnvf7jUiGqOO807RccUE+8nTgFCyWKPOyHLcUXGR/M0kPRqd200sQ==; bm_sv=71FE52A6189E40AE21C4A119FE10BFC0~YAAQ+9MXAsyMJjCTAQAAMDEtZBnmUdeIgL4QKgYw0UvbpHDxUNX63BX8ThJF3+974+mx5OLpCsEUVGeP2wt8Eg4IzUSGe8Vtj6OUyc5MtsIPBAquxHxwteZtAw9JRfq30d7YfHnwFf8ItZOjGgarDXAkIF6NwsUIvWFuwHHVQgugVU8+FljA1S3V1C09RU0312L4USx6JPFLnydHkvx7Tw1GNs7tjDm3OFnHqdZPHYnqsN9bs/Bpn+e68wfUl/I=~1; bm_sz=91B6DB17BCC9D0FE33B8EE77E5434914~YAAQ+9MXApsnJjCTAQAArGYoZBmiUe5/itivSr45SEEceRqq4sxpoTWR6n+NcTjkCYs5d3wzpItQa2dg5MWaxdm1wuJC99csU47MRKuwON0rbO92+TNMVlL7oUQgdmb6+jMVoOhJug+v76Cg7x2t8w0DsvfUkPFmSzFHpATNB9K479+0VSfdF5dNx9uBl5G/YuR9B3P2ESWPPSlaZnaOEtKseBKhRrJl9kVr26WTb7uuyV+MB/57UNnhAh+arbfAOQcymkiuClNCr8A+mbtNRGwRF6K2tbNTx+V9CRcQK3CVEbPyKDubNPj91VTaroJgvLMNhm7DRtHvkhRpq52vIUuIVMP41GmQEvUkhd0=~3290933~4600121',
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
