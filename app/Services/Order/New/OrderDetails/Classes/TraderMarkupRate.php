<?php

namespace App\Services\Order\OrderDetails\Classes;

use App\Services\Order\OrderDetails\Values\Gateway;
use App\Services\Order\OrderDetails\Values\Trader;
use Illuminate\Support\Carbon;

class TraderMarkupRate
{
    public static function calculate(Gateway $gateway, Trader $trader): float
    {
        $commission_rate = $gateway->traderMarkupRate;

        $personalMarkup = array_filter($trader->exchangeMarkupRates, function ($value) use ($gateway) {
            return $value['id'] === $gateway->id;
        });

        $personalMarkup = array_values($personalMarkup);

        if (! empty($personalMarkup[0]['markup_rate'])) {
            $commission_rate = (float)$personalMarkup[0]['markup_rate'];
        }

        $primeTimeBonus = services()->settings()->getPrimeTimeBonus();
        $start = Carbon::createFromTimeString($primeTimeBonus->starts);
        $end = Carbon::createFromTimeString($primeTimeBonus->ends);

        if (now()->between($start, $end)) {
            return round($commission_rate + $primeTimeBonus->rate, 2);
        }

        return $commission_rate;
    }
}
