<?php

namespace App\Http\Controllers;

use App\Contracts\OrderServiceContract;
use App\DTO\Order\OrderCreateDTO;
use App\Enums\OrderStatus;
use App\Exceptions\OrderException;
use App\Http\Requests\Payment\StoreRequest;
use App\Http\Resources\OrderResource;
use App\Http\Resources\PaymentGatewayResource;
use App\Models\Merchant;
use App\Services\Money\Currency;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class PaymentController extends Controller
{
    public function index()
    {
        $statuses = request()->input('filters.statuses', '');
        $statuses = explode(',', $statuses);

        foreach ($statuses as $key => $value) {
            if (! OrderStatus::tryFrom($value)) {
                unset($statuses[$key]);
            }
        }

        $externalID = request()->input('filters.external_id');
        $uuid = request()->input('filters.uuid');

        $orders = queries()->order()->paginateForMerchant(auth()->user(), $statuses, $externalID, $uuid);

        $orders = OrderResource::collection($orders);

        $orderStatuses = [];
        foreach (OrderStatus::values() as $status) {
            $orderStatuses[] = [
                'name' => trans("order.status.{$status}"),
                'value' => $status,
            ];
        }

        $currentFilters = [
            'statuses' => $statuses,
            'externalID' => $externalID,
            'uuid' => $uuid,
        ];

        return Inertia::render('Payment/Index', compact('orders', 'orderStatuses', 'currentFilters'));
    }

    public function create()
    {
        $paymentGateways = PaymentGatewayResource::collection(queries()->paymentGateway()->getAllActive())->resolve();

        $currencies = Currency::getAll()->transform(function ($currency) {
            return [
                'code' => strtoupper($currency->getCode()),
                'name' => strtoupper($currency->getCode()) . ' - ' . $currency->getName(),
            ];
        })->toArray();

        $merchants = Merchant::query()
            ->where('user_id', auth()->user()->id)
            ->whereNotNull('validated_at')
            ->whereNull('banned_at')
            ->where('active', true)
            ->orderByDesc('id')
            ->get()
            ->transform(function (Merchant $merchant) {
                $data['id'] = $merchant->id;
                $data['name'] = $merchant->name;

                return $data;
            });

        return Inertia::render('Payment/Add', compact('paymentGateways', 'currencies', 'merchants'));
    }

    public function store(StoreRequest $request)
    {
        $merchant = Merchant::where('id', $request->merchant_id)->first();

        Gate::authorize('access-to-merchant', $merchant);

        try {
            make(OrderServiceContract::class)->create(
                OrderCreateDTO::makeFromRequest(
                    $request->all() + ['merchant' => $merchant],
                )
            );
        } catch (OrderException $e) {
            return redirect()->back()->with('message', $e->getMessage());
        }

        return redirect()->route('payments.index');
    }
}
