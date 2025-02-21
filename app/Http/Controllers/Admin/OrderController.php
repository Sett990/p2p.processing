<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\PaymentDetail;
use App\Services\Money\Currency;
use App\Services\Money\Money;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class OrderController extends Controller
{
    public function index()
    {
        /*$averageAmount = Order::query()//TODO продолжим позже
            ->average('profit');
        $averageAmount = Money::fromUnits(intval($averageAmount), Currency::USDT());

        $conversionPrice = services()->market()->getBuyPrice(Currency::RUB());

        $detailsAvailableVolume = PaymentDetail::query()
            ->where('is_active', true)
            ->whereRelation('user', 'is_online', true)
            ->whereRelation('user', 'banned_at', null)
            ->where('currency', Currency::RUB())
            ->whereRaw('daily_limit - current_daily_limit > ?', [$averageAmount->convertBack($conversionPrice, Currency::USDT())->toUnits()])
            ->sum(DB::raw('daily_limit - current_daily_limit'));
        $detailsAvailableVolume = Money::fromUnits(intval($detailsAvailableVolume), Currency::RUB());

        dump($detailsAvailableVolume->toBeauty());*/

        $filters = $this->getTableFilters();
        $filtersVariants = $this->getFiltersData();

        $orders = queries()->order()->paginateForAdmin($filters);
        $orders = OrderResource::collection($orders);

        return Inertia::render('Order/Index', compact('orders', 'filters', 'filtersVariants'));
    }

    public function updateAmount(Request $request, Order $order)
    {
        Gate::authorize('access-to-order', $order);

        $request->validate([
            'amount' => ['required', 'integer', 'min:1'],
        ]);

        if (auth()->user()->hasRole('Trader')) {
            $onePercent = $order->amount->div(100)->toInt();
            $changedAmount = $order->amount->sub($request->amount)->abs()->toInt();
            if ($changedAmount > $onePercent) {
                return redirect()->back()->with('error', "Сумму можно изменить не более чем на 1% ($onePercent {$order->currency->getSymbol()})");
            }
        }

        services()->order()->updateAmount(
            order: $order,
            amount: Money::fromPrecision($request->input('amount'), $order->currency),
        );
    }
}
