<?php

namespace App\Http\Controllers;

use App\Enums\DetailType;
use App\Http\Requests\PayoutOffer\StoreRequest;
use App\Http\Requests\PayoutOffer\UpdateRequest;
use App\Http\Resources\PaymentGatewayResource;
use App\Http\Resources\PayoutOfferResource;
use App\Models\PaymentGateway;
use App\Models\PayoutOffer;
use App\Services\Money\Currency;
use App\Services\Money\Money;
use Inertia\Inertia;

class PayoutOfferController extends Controller
{
    public function index()
    {
        $payoutOffers = PayoutOffer::query()
            ->where('owner_id', auth()->id())
            ->orderByDesc('id')
            ->paginate(10);
        $payoutOffers = PayoutOfferResource::collection($payoutOffers);

        return Inertia::render('PayoutOffer/Index', compact('payoutOffers'));
    }

    public function create()
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
            if (DetailType::CARD->equals($detailType) || DetailType::PHONE->equals($detailType)) {
                $detailTypes[] = [
                    'name' => trans('detail-type.'.$detailType),
                    'code' => $detailType,
                ];
            }
        }

        $paymentGateways = queries()->paymentGateway()->getAllActive();
        $paymentGateways = PaymentGatewayResource::collection($paymentGateways)->resolve();

        return Inertia::render('PayoutOffer/AddEdit', compact('currencies', 'detailTypes', 'paymentGateways'));
    }

    public function store(StoreRequest $request)
    {
        $currency = PaymentGateway::find($request->input('payment_gateway_id'))->currency;
        PayoutOffer::create([
                'min_amount' => Money::fromPrecision($request->input('min_amount'), $currency),
                'max_amount' => Money::fromPrecision($request->input('max_amount'), $currency),
                'owner_id' => auth()->id(),
                'currency' => $currency->getCode(),
            ] + $request->validated());
    }

    public function edit(PayoutOffer $payoutOffer)
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
            if (DetailType::CARD->equals($detailType) || DetailType::PHONE->equals($detailType)) {
                $detailTypes[] = [
                    'name' => trans('detail-type.'.$detailType),
                    'code' => $detailType,
                ];
            }
        }

        $paymentGateways = queries()->paymentGateway()->getAllActive();
        $paymentGateways = PaymentGatewayResource::collection($paymentGateways)->resolve();
        $payoutOffer = PayoutOfferResource::make($payoutOffer)->resolve();

        return Inertia::render('PayoutOffer/AddEdit', compact('currencies', 'detailTypes', 'paymentGateways', 'payoutOffer'));
    }

    public function update(UpdateRequest $request, PayoutOffer $payoutOffer)
    {
        $currency = PaymentGateway::find($request->input('payment_gateway_id'))->currency;
        $payoutOffer->update([
                'min_amount' => Money::fromPrecision($request->input('min_amount'), $currency),
                'max_amount' => Money::fromPrecision($request->input('max_amount'), $currency),
                'currency' => $currency->getCode(),
            ] + $request->validated());
    }
}
