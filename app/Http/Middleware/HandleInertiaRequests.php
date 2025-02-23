<?php

namespace App\Http\Middleware;

use App\Enums\DisputeStatus;
use App\Enums\OrderStatus;
use App\Http\Resources\WalletResource;
use App\Models\Dispute;
use App\Models\Order;
use App\Services\Money\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
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
        $rates = cache()->remember('currency-rates', 60, function () {
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

        $orderQuery = Order::query()
            ->where('status', OrderStatus::PENDING);
        $disputeQuery = Dispute::query()
            ->where('status', DisputeStatus::PENDING);

        if (isRouteFor('Merchant')) {
            $pendingOrdersCount = 0;
            $pendingDisputesCount = 0;
        } elseif (isRouteFor('Trader')) {
            $pendingOrdersCount = $orderQuery->clone()->whereRelation('paymentDetail', 'user_id', auth()->id())->count();
            $pendingDisputesCount = $disputeQuery->clone()->whereRelation('order.paymentDetail', 'user_id', auth()->id())->count();
        } elseif (isRouteFor('Super Admin')) {
            $pendingOrdersCount = $orderQuery->clone()->count();
            $pendingDisputesCount = $disputeQuery->clone()->count();;
        } else {
            $pendingOrdersCount = 0;
            $pendingDisputesCount = 0;
        }

        $menu = [
            'pendingOrdersCount' => $pendingOrdersCount,
            'pendingDisputesCount' => $pendingDisputesCount,
        ];

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
                'wallet' => fn () => $request->user() ? WalletResource::make($request->user()->wallet)->resolve() : null,
                'hasPendingDisputes' => fn () => $request->user()?->hasRole('Trader') ? $menu['pendingDisputesCount'] > 0 : 0,
            ],
            'menu' => $menu
        ];
    }
}
