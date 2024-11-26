<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Money\Currency;
use Inertia\Inertia;

class CurrencyController extends Controller
{
    public function index()
    {
        $currencies = Currency::getAll()
            ->transform(function ($currency) {
                return [
                    'code' => $currency->getCode(),
                    'symbol' => $currency->getSymbol(),
                    'name' => $currency->getName(),
                    'buy_price' => services()->market()->getBuyPrice($currency)->toPrecision(),
                    'sell_price' => services()->market()->getSellPrice($currency)->toPrecision(),
                ];
            })->toArray();

        return Inertia::render('Currency/Index', compact('currencies'));
    }
}
