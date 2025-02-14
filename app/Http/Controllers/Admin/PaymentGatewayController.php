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

    public function create()
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

        $paymentGateways = PaymentGatewayResource::collection(queries()->paymentGateway()->getAllActive())->resolve();

        return Inertia::render('PaymentGateway/Add', compact('currencies', 'detailTypes', 'paymentGateways'));
    }

    public function store(StoreRequest $request)
    {
        $logo = $request->file('logo');
        $logo_name = 'logo_'.strtolower(Str::random(32)).'.'.$logo->extension();
        $logo->move(storage_path('/app/public/logos'), $logo_name);

        $data = $request->validated();
        $data['sms_senders'] = $data['sms_senders'] ?? [];
        $data['sub_payment_gateways'] = $data['sub_payment_gateways'] ?? [];
        $data['logo'] = $logo_name;

        $gateway = PaymentGateway::create($data);

        \App\Models\Merchant::all()->each(function (\App\Models\Merchant $merchant) use ($gateway) {
            $gatewaySettings = $merchant->gateway_settings;

            foreach ($gatewaySettings as $gatewaySetting) {
                if (! isset($gatewaySettings[$gateway->id])) {
                    $gatewaySettings[$gateway->id] = [
                        'merchant_commission' => (float)$gateway->order_service_commission_rate,
                        'active' => true,
                    ];
                }
            }

            $merchant->update([
                'gateway_settings' => $gatewaySettings
            ]);
        });

        return redirect()->route('admin.payment-gateways.index');
    }

    public function edit(PaymentGateway $paymentGateway)
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

        $paymentGateways = PaymentGatewayResource::collection(queries()->paymentGateway()->getAllActive())->resolve();

        $paymentGateway->code = preg_replace('/_[a-z]{1,}$/', '', $paymentGateway->code);

        $paymentGateway = PaymentGatewayResource::make($paymentGateway)->resolve();

        return Inertia::render('PaymentGateway/Edit', compact('paymentGateway', 'currencies', 'detailTypes', 'paymentGateways'));
    }

    public function update(UpdateRequest $request, PaymentGateway $paymentGateway)
    {
        $data = $request->validated();
        $data['sms_senders'] = $data['sms_senders'] ?? [];
        $data['sub_payment_gateways'] = $data['sub_payment_gateways'] ?? [];

        $logo = $request->file('logo');
        if ($logo) {
            $logo_name = 'logo_'.strtolower(Str::random(32)).'.'.$logo->extension();
            $logo->move(storage_path('/app/public/logos'), $logo_name);
            $data['logo'] = $logo_name;
        } else {
            unset($data['logo']);
        }

        $paymentGateway->update($data);

        \App\Models\Merchant::all()->each(function (\App\Models\Merchant $merchant) use ($paymentGateway) {
            $gatewaySettings = $merchant->gateway_settings;

            if ((float)$gatewaySettings[$paymentGateway->id]['merchant_commission'] > (float)$paymentGateway->order_service_commission_rate) {
                $gatewaySettings[$paymentGateway->id]['merchant_commission'] = (float)$paymentGateway->order_service_commission_rate;
            }

            $merchant->update([
                'gateway_settings' => $gatewaySettings
            ]);
        });

        return redirect()->route('admin.payment-gateways.index');
    }
}
