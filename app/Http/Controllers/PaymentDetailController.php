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
use App\Services\Money\Currency;
use App\Utils\Transaction;
use App\DTO\PaymentDetail\PaymentDetailCreateDTO;
use App\DTO\PaymentDetail\PaymentDetailUpdateDTO;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class PaymentDetailController extends Controller
{
    public function index()
    {
        $filters = $this->getTableFilters();
        $filtersVariants = $this->getFiltersData();

        $fromArchive = request()->tab === 'archived';

        $paymentDetails = queries()->paymentDetail()->paginateForUser(auth()->user(), $filters, $fromArchive);

        $paymentDetails = PaymentDetailResource::collection($paymentDetails);

        return Inertia::render('PaymentDetail/Index', compact('paymentDetails', 'filters', 'filtersVariants'));
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
        // Проверяем принадлежность устройства пользователю
        $device = UserDevice::where('id', $request->user_device_id)
            ->where('user_id', auth()->id())
            ->first();

        if (!$device) {
            return;
        }

        $dto = PaymentDetailCreateDTO::makeFromRequest($request->validated() + [
            'user_id' => auth()->id(),
        ]);
        services()->paymentDetail()->create($dto);

        return redirect()->route('payment-details.index');
    }

    public function edit(PaymentDetail $paymentDetail)
    {
        Gate::authorize('access-to-payment-detail', $paymentDetail);

        $paymentDetail->load(['user', 'userDevice']);
        $paymentDetail->loadCount(['orders as pending_orders_count' => function ($query) {
            $query->where('status', OrderStatus::PENDING);
        }]);

        $paymentDetail->setAttribute('payment_gateway_ids', $paymentDetail->paymentGateways()->pluck('payment_gateways.id')->toArray());

        $devices = UserDeviceResource::collection(
            UserDevice::where('user_id', $paymentDetail->user_id)->get()
        )->resolve();

        $paymentDetail = PaymentDetailResource::make($paymentDetail)->resolve();

        $paymentGateways = PaymentGatewayResource::collection(queries()->paymentGateway()->getAllActive())->resolve();

        return Inertia::render('PaymentDetail/Edit', compact('paymentDetail', 'paymentGateways', 'devices'));
    }

    public function update(UpdateRequest $request, PaymentDetail $paymentDetail)
    {
        Gate::authorize('access-to-payment-detail', $paymentDetail);

        // Проверяем принадлежность устройства пользователю
        $device = UserDevice::where('id', $request->user_device_id)
            ->where('user_id', $paymentDetail->user_id)
            ->first();

        if (!$device) {
            return;
        }

        // Получаем текущие ID платежных методов
        $currentPaymentGatewayIds = $paymentDetail->paymentGateways()->pluck('payment_gateways.id')->toArray();

        // Проверяем, что все текущие ID присутствуют в новом списке
        $missingIds = array_diff($currentPaymentGatewayIds, $request->payment_gateway_ids);
        if (!empty($missingIds)) {
            return redirect()->back()->withErrors([
                'payment_gateway_ids' => 'Нельзя удалить уже выбранные платежные методы'
            ]);
        }

        $dto = PaymentDetailUpdateDTO::makeFromRequest($request->validated());
        services()->paymentDetail()->update($dto, $paymentDetail);
    }

    public function toggleActive(PaymentDetail $paymentDetail)
    {
        Gate::authorize('access-to-payment-detail', $paymentDetail);

        services()->paymentDetail()->toggleActive($paymentDetail);
    }
}
