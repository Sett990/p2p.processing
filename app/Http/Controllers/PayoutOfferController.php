<?php

namespace App\Http\Controllers;

use App\Enums\DetailType;
use App\Http\Requests\PayoutOffer\StoreRequest;
use App\Http\Resources\PaymentGatewayResource;
use App\Http\Resources\PayoutOfferResource;
use App\Models\PaymentGateway;
use App\Models\PayoutOffer;
use App\Services\Money\Currency;
use App\Services\Money\Money;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PayoutOfferController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $currencies = Currency::getAll()
            ->transform(function (Currency $currency) {
                return [
                    'code' => $currency->getCode(),
                    'symbol' => $currency->getSymbol(),
                    'name' => $currency->getName(),
                ];
            })->toArray();

        $detailTypes = [];
        foreach (DetailType::values() as $detailType) {
            $detailTypes[] = [
                'name' => trans('detail-type.'.$detailType),
                'code' => $detailType,
            ];
        }

        $paymentGateways = queries()->paymentGateway()->getAllActive();
        $payoutOffers = PayoutOffer::query()->where('owner_id', auth()->id())->paginate(10);

        $paymentGateways = PaymentGatewayResource::collection($paymentGateways)->resolve();
        $payoutOffers = PayoutOfferResource::collection($payoutOffers);

        return Inertia::render('PayoutOffer/Index', compact('currencies', 'detailTypes', 'paymentGateways', 'payoutOffers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        $currency = PaymentGateway::find($request->input('payment_gateway_id'))->currency;
        PayoutOffer::create($request->validated() + [
                'min_amount' => Money::fromPrecision($request->input('min_amount'), $currency),
                'max_amount' => Money::fromPrecision($request->input('max_amount'), $currency),
                'owner_id' => auth()->id(),
                'currency' => $currency->getCode(),
            ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(PayoutOffer $payoutOffer)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PayoutOffer $payoutOffer)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PayoutOffer $payoutOffer)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PayoutOffer $payoutOffer)
    {
        //
    }
}
