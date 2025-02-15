<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Market;
use App\Http\Controllers\Controller;
use App\Services\Money\Currency;
use Inertia\Inertia;

class CurrencyController extends Controller
{
    public function index()
    {
        $markets = [];

        foreach (Market::cases() as $market) {
            $currencies = [];

            Currency::getAll()
                ->map(function (Currency $currency) use (&$currencies, $market) {
                    if ($market->equals(Market::GARANTEX) && $currency->notEquals(Currency::RUB())) {
                        return;
                    }

                    $currencies[] = [
                        'code' => $currency->getCode(),
                        'symbol' => $currency->getSymbol(),
                        'name' => $currency->getName(),
                        'buy_price' => services()->market()->getBuyPrice($currency, $market)->toPrecision(),
                        'sell_price' => services()->market()->getSellPrice($currency, $market)->toPrecision(),
                    ];
                });

            $markets[$market->value] = $currencies;
        }

        return Inertia::render('Currency/Index', compact('markets'));
    }
}
