<?php

namespace App\Services\Market;

use App\Contracts\MarketServiceContract;
use App\Enums\Market;
use App\Jobs\LoadConversionPricesJob;
use App\Services\Market\Utils\Parser\ByBitParser;
use App\Services\Money\Currency;
use App\Services\Market\Utils\MarketStore;
use App\Services\Market\Utils\Parser\Parser;
use App\Services\Money\Money;
use Throwable;

class MarketService implements MarketServiceContract
{
    protected Parser  $parser;

    public function __construct()
    {
        $this->parser = new Parser();
    }

    public function loadAllPrices(): void
    {
        Currency::getAll()
            ->each(function (Currency $currency) {
                foreach (Market::cases() as $market) {
                    if ($market->equals(Market::GARANTEX) && $currency->notEquals(Currency::RUB())) { //TODO для GARANTEX только рубли
                        continue;
                    }

                    LoadConversionPricesJob::dispatch($currency, $market);
                }
            });
    }

    public function loadPricesFor(Currency $currency, Market $market = Market::BYBIT): void
    {
        try {
            $prices = $this->parser->getPrices($currency, $market);

            MarketStore::putPrice(
                currency: $currency,
                market: $market,
                buy_price: $prices->buyPrice->toUnits(),
                sell_price: $prices->sellPrice->toUnits()
            );
        } catch (Throwable $e) {
            //do nothing
        }
    }

    public function getSellPrice(Currency $currency, Market $market = Market::BYBIT): Money
    {
        $price = MarketStore::getSellPrice($currency, $market);

        return new Money($price, $currency);
    }

    public function getBuyPrice(Currency $currency, Market $market = Market::BYBIT): Money
    {
        $price = MarketStore::getBuyPrice($currency, $market);

        return new Money($price, $currency);
    }

    public function loadPaymentMethodsList(): void
    {
        $methods = (new ByBitParser())->parsePaymentMethodsList();

        MarketStore::putPaymentMethodsList($methods);
    }

    public function getPaymentMethods(Currency $currency): array
    {
        $paymentList = MarketStore::getPaymentMethodsList();

        $currencyPaymentIdMap = json_decode($paymentList['currencyPaymentIdMap'], true);
        $paymentConfigVo = $paymentList['paymentConfigVo'];
        $currencyPaymentIdMapForCurrentCurrency = $currencyPaymentIdMap[strtoupper($currency->getCode())];

        $methods = [];

        foreach ($paymentConfigVo as $paymentMethod) {
            if (in_array($paymentMethod['paymentType'], $currencyPaymentIdMapForCurrentCurrency)) {
                $methods[] = [
                    'id' => $paymentMethod['paymentType'],
                    'name' => $paymentMethod['paymentName'],
                ];
            }
        }

        return $methods;
    }
}
