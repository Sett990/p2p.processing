<?php

namespace App\Http\Middleware;

use App\Http\Resources\WalletResource;
use App\Services\Money\Currency;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): string|null
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $rates = cache()->remember('currency-rates', 15, function () {
            return Currency::getAll()
                ->transform(function (Currency $currency) {
                    return [
                        'code' => $currency->getCode(),
                        'buy_price' => services()->market()->getBuyPrice($currency)->toPrecision(),
                        'sell_price' => services()->market()->getSellPrice($currency)->toPrecision(),
                    ];
                })
                ->sort(function ($currency) {
                    return in_array($currency['code'], ['rub', 'usd', 'eur']);
                })
                ->reverse()
                ->values()
                ->toArray();
        });

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
                'role' => $request->user()?->roles()?->first(),
                'is_admin' => $request->user()?->hasRole('Super Admin'),
                'is_impersonated' => $request->user()?->isImpersonated()
            ],
            'ziggy' => fn () => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
            'flash' => [
                'message' => fn () => $request->session()->get('message'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'data' => [
                'rates' => fn () => $rates,
                'wallet' => fn () => $request->user() ? WalletResource::make($request->user()->wallet)->resolve() : null
            ]
        ];
    }
}
