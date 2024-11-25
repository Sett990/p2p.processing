<?php

namespace App\Services\Market;

use App\Contracts\MarketServiceContract;
use App\Jobs\LoadConversionPricesJob;
use App\Services\Money\Currency;
use App\Services\Market\Utils\MarketStore;
use App\Services\Market\Utils\Parser;
use App\Services\Money\Money;

class MarketService implements MarketServiceContract
{
    protected Parser $parser;

    public function __construct()
    {
        $this->parser = new Parser();
    }

    public function loadAllPrices(): void
    {
        Currency::getAll()
            ->each(function (Currency $currency) {
                LoadConversionPricesJob::dispatch($currency);
            });
    }

    public function loadPricesFor(Currency $currency): void
    {
        MarketStore::putPrice(
            currency: $currency->getCode(),
            buy_price: $this->parser->parseBuyPrice($currency)->toUnits(),
            sell_price: $this->parser->parseSellPrice($currency)->toUnits()
        );
    }

    public function getSellPrice(Currency $currency): Money
    {
        $price = MarketStore::getSellPrice($currency);

        return new Money($price, $currency);
    }

    public function getBuyPrice(Currency $currency): Money
    {
        $price = MarketStore::getBuyPrice($currency);

        return new Money($price, $currency);
    }

    public function loadPaymentMethodsList(): void
    {
        $methods = $this->parser->parsePaymentMethodsList();

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
