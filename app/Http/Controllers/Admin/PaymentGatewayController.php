<?php

namespace App\Http\Controllers\Admin;

use App\Enums\DetailType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PaymentGateway\StoreRequest;
use App\Http\Requests\Admin\PaymentGateway\UpdateRequest;
use App\Http\Resources\PaymentGatewayResource;
use App\Models\PaymentGateway;
use App\Services\Money\Currency;
use Illuminate\Support\Str;
use Inertia\Inertia;

class PaymentGatewayController extends Controller
{
    public function index()
    {
        $filters = $this->getTableFilters();

        $paymentGateways = queries()->paymentGateway()->paginateForAdmin($filters);

        $paymentGateways = PaymentGatewayResource::collection($paymentGateways);

        return Inertia::render('PaymentGateway/Index', compact('paymentGateways', 'filters'));
    }

    public function createData()
    {
        $currencies = Currency::getAll()->transform(function ($currency) {
            return ['code' => strtoupper($currency->getCode())];
        })->toArray();

        $detailTypes = [];
        foreach (DetailType::values() as $detailType) {
            $detailTypes[] = [
                'name' => trans('detail-type.'.$detailType),
                'code' => $detailType,
            ];
        }

        $primeTimeCommissionRate = services()->settings()->getPrimeTimeBonus()->rate;

        return response()->json([
            'success' => true,
            'data' => compact('currencies', 'detailTypes', 'primeTimeCommissionRate'),
        ]);
    }

    public function store(StoreRequest $request)
    {
        $logo = $request->file('logo');
        $logo_name = 'logo_'.strtolower(Str::random(32)).'.'.$logo->extension();
        $logo->move(storage_path('/app/public/logos'), $logo_name);

        $data = $request->validated();

        $data['sms_senders'] = $data['sms_senders'] ?? [];
        $data['logo'] = $logo_name;

        PaymentGateway::create($data);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
            ], 201);
        }
        return redirect()->route('admin.payment-gateways.index');
    }

    public function editData(PaymentGateway $paymentGateway)
    {
        $currencies = Currency::getAll()->transform(function ($currency) {
            return ['code' => strtoupper($currency->getCode())];
        })->toArray();

        $detailTypes = [];
        foreach (DetailType::values() as $detailType) {
            $detailTypes[] = [
                'name' => trans('detail-type.'.$detailType),
                'code' => $detailType,
            ];
        }

        $paymentGateway = PaymentGatewayResource::make($paymentGateway)->resolve();

        return response()->json([
            'success' => true,
            'data' => compact('paymentGateway', 'currencies', 'detailTypes'),
        ]);
    }

    public function update(UpdateRequest $request, PaymentGateway $paymentGateway)
    {
        $data = $request->validated();
        $data['sms_senders'] = $data['sms_senders'] ?? [];

        $logo = $request->file('logo');
        if ($logo) {
            $logo_name = 'logo_'.strtolower(Str::random(32)).'.'.$logo->extension();
            $logo->move(storage_path('/app/public/logos'), $logo_name);
            $data['logo'] = $logo_name;
        } else {
            unset($data['logo']);
        }

        $paymentGateway->update($data);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
            ]);
        }
        return redirect()->route('admin.payment-gateways.index');
    }
}
