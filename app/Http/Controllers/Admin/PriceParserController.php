<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PriceParser\UpdateRequest;
use App\Models\ValueObjects\Settings\CurrencyPriceParserSettings;
use App\Models\ValueObjects\Settings\CurrencyPriceParserSideSettings;
use App\Services\Money\Currency;
use Inertia\Inertia;

class PriceParserController extends Controller
{
    public function editData(string $currency)
    {
        $currency = new Currency($currency);
        $methods = services()
            ->market()
            ->getPaymentMethods($currency);
        $settings = services()
            ->settings()
            ->getCurrencyPriceParser($currency)
            ->toArray();

        return response()->json([
            'success' => true,
            'data' => [
                'currency' => $currency->getCode(),
                'methods' => $methods,
                'settings' => $settings,
            ],
        ]);
    }

    public function update(UpdateRequest $request, string $currency)
    {
        services()->settings()->updateCurrencyPriceParser(
            currency: new Currency($currency),
            settings: new CurrencyPriceParserSettings(
                buy: CurrencyPriceParserSideSettings::fromArray($request->validated()['buy'] ?? []),
                sell: CurrencyPriceParserSideSettings::fromArray($request->validated()['sell'] ?? []),
            )
        );

        return response()->json(['success' => true]);
    }
}
