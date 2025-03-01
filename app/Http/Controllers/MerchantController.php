<?php

namespace App\Http\Controllers;

use App\Enums\MarketEnum;
use App\Enums\OrderStatus;
use App\Http\Requests\Merchant\StoreRequest;
use App\Http\Requests\Merchant\UpdateGatewaySettingsRequest;
use App\Http\Resources\MerchantResource;
use App\Http\Resources\OrderResource;
use App\Http\Resources\PaymentGatewayResource;
use App\Models\Merchant;
use App\Models\Order;
use App\Services\Money\Currency;
use App\Services\Money\Money;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Inertia\Inertia;

class MerchantController extends Controller
{
    public function index()
    {
        $merchants = Merchant::query()
            ->with('user')
            ->withSum(['orders' => function ($query) {
                $query->where('status', OrderStatus::SUCCESS);
                $query->whereDate('created_at', now()->today());
            }], 'merchant_profit')
            ->where('user_id', auth()->user()->id)
            ->orderByDesc('id')
            ->paginate(9);

        $merchants->transform(function (Merchant $merchant) {
            $merchant->orders_sum_merchant_profit = $merchant->orders_sum_merchant_profit ?? 0;
            return $merchant;
        });

        $merchants = MerchantResource::collection($merchants);

        return Inertia::render('Merchant/Index', compact('merchants'));
    }

    public function show(Merchant $merchant)
    {
        Gate::authorize('access-to-merchant', $merchant);

        $orders = Order::query()
            ->with(['merchant'])
            ->where('merchant_id', $merchant->id)
            ->where('status', OrderStatus::SUCCESS)
            ->orderByDesc('id')
            ->paginate(10);

        $paymentGateways = queries()->paymentGateway()->getAllActive();

        $orders = OrderResource::collection($orders);
        $paymentGateways = PaymentGatewayResource::collection($paymentGateways);

        $today = Order::query()
                ->where('status', OrderStatus::SUCCESS)
                ->where('merchant_id', $merchant->id)
                ->whereDate('created_at', now()->today());

        $yesterday = Order::query()
                ->where('status', OrderStatus::SUCCESS)
                ->where('merchant_id', $merchant->id)
                ->whereDate('created_at', now()->yesterday());

        $month = Order::query()
                ->where('status', OrderStatus::SUCCESS)
                ->where('merchant_id', $merchant->id)
                ->whereDate('created_at', '>', now()->startOfMonth());

        $total = Order::query()
                ->where('status', OrderStatus::SUCCESS)
                ->where('merchant_id', $merchant->id);

        $statistics = [
            'today_profit' => Money::fromUnits($today->sum('merchant_profit') ?? 0, Currency::USDT())->toBeauty(),
            'yesterday_profit' => Money::fromUnits($yesterday->sum('merchant_profit') ?? 0, Currency::USDT())->toBeauty(),
            'month_profit' => Money::fromUnits($month->sum('merchant_profit') ?? 0, Currency::USDT())->toBeauty(),
            'total_profit' => Money::fromUnits($total->sum('merchant_profit') ?? 0, Currency::USDT())->toBeauty(),
            'today_orders_count' => $today->count('id'),
            'yesterday_orders_count' => $yesterday->count('id'),
            'month_orders_count' => $month->count('id'),
            'total_orders_count' => $total->count('id'),
            'currency' => Currency::USDT()->getCode(),
        ];

        $merchant = MerchantResource::make($merchant)->resolve();

        $markets = [];
        foreach (MarketEnum::cases() as $market) {
            $markets[] = [
                'name' =>  trans("market.name.{$market->value}"),
                'value' => $market->value,
            ];
        }

        $exchangeRateMarkup = [];

        Currency::getAll()
            ->map(function (Currency $currency) use (&$exchangeRateMarkup) {
                $exchangeRateMarkup[] = [
                    'currency' => $currency->getCode(),
                    'markup' => null,
                ];
            });

        return Inertia::render('Merchant/Show', compact('merchant', 'orders', 'paymentGateways', 'statistics', 'markets', 'exchangeRateMarkup'));
    }

    public function create()
    {
        return Inertia::render('Merchant/Add');
    }

    public function store(StoreRequest $request)
    {
        $merchant = Merchant::create([
            'uuid' => (string)Str::uuid(),
            'user_id' => auth()->id(),
            'active' => true,
            'name' => $request->name,
            'description' => $request->description,
            'domain' => parse_url($request->project_link)['host'],
            'gateway_settings' => [],
            'market' => MarketEnum::BYBIT,
        ]);

        return redirect()->route('merchants.show', $merchant->id);
    }

    public function updateCallbackURL(Request $request, Merchant $merchant)
    {
        Gate::authorize('access-to-merchant', $merchant);

        $request->validate(['callback_url' => ['nullable', 'string', 'url:https', 'max:256']]);

        $merchant->update([
            'callback_url' => $request->callback_url
        ]);
    }

    public function updateGatewaySettings(UpdateGatewaySettingsRequest $request, Merchant $merchant)
    {
        Gate::authorize('access-to-merchant', $merchant);

        dd($request->validated());

        $merchant->update($request->validated());
    }
}
