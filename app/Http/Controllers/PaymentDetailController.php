<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Http\Requests\PaymentDetail\StoreRequest;
use App\Http\Requests\PaymentDetail\UpdateRequest;
use App\Http\Resources\PaymentDetailResource;
use App\Http\Resources\PaymentGatewayResource;
use App\Http\Resources\UserDeviceResource;
use App\Models\PaymentDetail;
use App\Models\PaymentGateway;
use App\Models\UserDevice;
use App\Services\Money\Money;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class PaymentDetailController extends Controller
{
    public function index()
    {
        $filters = $this->getTableFilters();

        $fromArchive = request()->tab === 'archived';

        $paymentDetails = queries()->paymentDetail()->paginateForUser(auth()->user(), $filters, $fromArchive);

        $paymentDetails = PaymentDetailResource::collection($paymentDetails);

        return Inertia::render('PaymentDetail/Index', compact('paymentDetails', 'filters'));
    }

    public function create()
    {
        $paymentGateways = PaymentGatewayResource::collection(queries()->paymentGateway()->getAllActive())->resolve();
        $devices = UserDeviceResource::collection(
            UserDevice::where('user_id', auth()->id())->get()
        )->resolve();

        return Inertia::render('PaymentDetail/Add', compact('paymentGateways', 'devices'));
    }

    public function store(StoreRequest $request)
    {
        //TODO добавить format + regex валидацию через preg_match
        $currency = PaymentGateway::find($request->payment_gateway_id)->currency;

        PaymentDetail::create([
            'daily_limit' => Money::fromPrecision($request->daily_limit, $currency),
            'user_id' => auth()->id(),
            'currency' => $currency,
            'last_used_at' => now(),
            'user_device_id' => $request->user_device_id,
        ] + $request->validated());
    }

    public function edit(PaymentDetail $paymentDetail)
    {
        $paymentDetail->load(['user', 'paymentGateway', 'userDevice']);

        Gate::authorize('access-to-payment-detail', $paymentDetail);

        $paymentDetail->load('paymentGateway');
        $paymentDetail->loadCount(['orders as pending_orders_count' => function ($query) {
            $query->where('status', OrderStatus::PENDING);
        }]);

        $paymentDetail = PaymentDetailResource::make($paymentDetail)->resolve();
        $paymentGateways = PaymentGatewayResource::collection(queries()->paymentGateway()->getAllActive())->resolve();
        $devices = UserDeviceResource::collection(
            UserDevice::where('user_id', auth()->id())->get()
        )->resolve();

        return Inertia::render('PaymentDetail/Edit', compact('paymentDetail', 'paymentGateways', 'devices'));
    }

    public function update(UpdateRequest $request, PaymentDetail $paymentDetail)
    {
        Gate::authorize('access-to-payment-detail', $paymentDetail);

        $paymentDetail->update([
            'daily_limit' => Money::fromPrecision($request->daily_limit, $paymentDetail->currency),
            'user_device_id' => $request->user_device_id,
        ] + $request->validated());
    }

    public function toggleActive(PaymentDetail $paymentDetail)
    {
        Gate::authorize('access-to-payment-detail', $paymentDetail);

        $paymentDetail->update([
            'is_active' => !$paymentDetail->is_active
        ]);
    }
}
