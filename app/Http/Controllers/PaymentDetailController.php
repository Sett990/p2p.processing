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
use App\Models\User;
use App\Models\UserDevice;
use App\Services\Money\Money;
use App\Services\Money\Currency;
use App\Utils\Transaction;
use App\DTO\PaymentDetail\PaymentDetailCreateDTO;
use App\DTO\PaymentDetail\PaymentDetailUpdateDTO;
use Illuminate\Http\Request;
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
        // legacy page endpoint is no longer used (migrated to modal + axios)
        abort(404);
    }

    public function createData(Request $request)
    {
        $paymentGateways = PaymentGatewayResource::collection(queries()->paymentGateway()->getAllActive())->resolve();

        $userId = auth()->id();
        $requestedUserId = (int) $request->input('user_id', 0);
        if ($requestedUserId > 0) {
            if ($requestedUserId !== auth()->id() && ! auth()->user()?->hasRole('Super Admin')) {
                abort(403);
            }
            $userId = $requestedUserId;
        }

        $user = User::findOrFail($userId);
        $canWorkWithoutDevice = (bool) $user->can_work_without_device;

        $devices = $canWorkWithoutDevice
            ? []
            : UserDeviceResource::collection(
                UserDevice::where('user_id', $userId)->get()
            )->resolve();

        return response()->json([
            'success' => true,
            'data' => [
                'paymentGateways' => $paymentGateways,
                'devices' => $devices,
                'canWorkWithoutDevice' => $canWorkWithoutDevice,
            ],
        ]);
    }

    public function store(StoreRequest $request)
    {
        $user = auth()->user();
        $deviceId = $request->user_device_id;
        $device = null;

        if ($deviceId) {
            $device = UserDevice::where('id', $deviceId)
                ->where('user_id', $user->id)
                ->first();
            if (! $device) {
                return $request->expectsJson()
                    ? response()->json([
                        'success' => false,
                        'errors' => [
                            'user_device_id' => ['Устройство не найдено или не принадлежит пользователю'],
                        ],
                    ], 422)
                    : redirect()->back()->withErrors([
                        'user_device_id' => 'Устройство не найдено или не принадлежит пользователю',
                    ]);
            }
        }

        if (! $deviceId && ! $user->can_work_without_device) {
            return $request->expectsJson()
                ? response()->json([
                    'success' => false,
                    'errors' => [
                        'user_device_id' => ['Необходимо выбрать устройство'],
                    ],
                ], 422)
                : redirect()->back()->withErrors([
                    'user_device_id' => 'Необходимо выбрать устройство',
                ]);
        }

        $dto = PaymentDetailCreateDTO::makeFromRequest($request->validated() + [
            'user_id' => auth()->id(),
        ]);
        services()->paymentDetail()->create($dto);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
            ]);
        }

        return redirect()->route('payment-details.index');
    }

    public function show(PaymentDetail $paymentDetail)
    {
        Gate::authorize('access-to-payment-detail', $paymentDetail);

        $paymentDetail->load(['user', 'userDevice', 'paymentGateways']);
        $paymentDetail->loadCount(['orders as pending_orders_count' => function ($query) {
            $query->where('status', OrderStatus::PENDING);
        }]);

        $paymentDetail->setAttribute('payment_gateway_ids', $paymentDetail->paymentGateways()->pluck('payment_gateways.id')->toArray());

        $paymentDetail = PaymentDetailResource::make($paymentDetail)->resolve();

        return response()->json([
            'success' => true,
            'data' => $paymentDetail,
        ]);
    }

    public function update(UpdateRequest $request, PaymentDetail $paymentDetail)
    {
        Gate::authorize('access-to-payment-detail', $paymentDetail);

        $owner = $paymentDetail->user;
        $deviceId = $request->user_device_id;
        if ($deviceId) {
            $device = UserDevice::where('id', $deviceId)
                ->where('user_id', $owner->id)
                ->first();

            if (! $device) {
                return $request->expectsJson()
                    ? response()->json([
                        'success' => false,
                        'errors' => [
                            'user_device_id' => ['Устройство не найдено или не принадлежит пользователю'],
                        ],
                    ], 422)
                    : redirect()->back()->withErrors([
                        'user_device_id' => 'Устройство не найдено или не принадлежит пользователю',
                    ]);
            }
        }

        if (! $deviceId && ! $owner->can_work_without_device) {
            return $request->expectsJson()
                ? response()->json([
                    'success' => false,
                    'errors' => [
                        'user_device_id' => ['Необходимо выбрать устройство'],
                    ],
                ], 422)
                : redirect()->back()->withErrors([
                    'user_device_id' => 'Необходимо выбрать устройство',
                ]);
        }

        // Получаем текущие ID платежных методов
        $currentPaymentGatewayIds = $paymentDetail->paymentGateways()->pluck('payment_gateways.id')->toArray();

        // Проверяем, что все текущие ID присутствуют в новом списке
        $missingIds = array_diff($currentPaymentGatewayIds, $request->payment_gateway_ids);
        if (!empty($missingIds)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => [
                        'payment_gateway_ids' => ['Нельзя удалить уже выбранные платежные методы']
                    ]
                ], 422);
            }
            return redirect()->back()->withErrors([
                'payment_gateway_ids' => 'Нельзя удалить уже выбранные платежные методы'
            ]);
        }

        $dto = PaymentDetailUpdateDTO::makeFromRequest($request->validated());
        services()->paymentDetail()->update($dto, $paymentDetail);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
            ]);
        }

        return redirect()->route('payment-details.index');
    }

    public function toggleActive(PaymentDetail $paymentDetail)
    {
        Gate::authorize('access-to-payment-detail', $paymentDetail);

        services()->paymentDetail()->toggleActive($paymentDetail);
    }
}
