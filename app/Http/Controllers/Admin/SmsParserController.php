<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SmsParser\StoreRequest;
use App\Http\Requests\Admin\SmsParser\UpdateRequest;
use App\Http\Resources\PaymentGatewayResource;
use App\Http\Resources\SmsParserResource;
use App\Models\PaymentGateway;
use App\Models\SmsParser;
use App\Services\Money\Currency;
use App\Services\Money\Money;
use App\Services\Sms\Utils\Parser;
use Illuminate\Database\Eloquent\Collection;
use Inertia\Inertia;

class SmsParserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $smsParsers = SmsParser::query()
            ->with('paymentGateway')
            ->orderByDesc('id')
            ->paginate(10);

        $smsParsers = SmsParserResource::collection($smsParsers);

        return Inertia::render('SmsParser/Index', compact('smsParsers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $paymentGateways = PaymentGatewayResource::collection($this->getPaymentGateways())->resolve();

        $currencies = Currency::getAll()
            ->transform(function ($currency) {
                return ['code' => $currency->getCode()];
            });

        return Inertia::render('SmsParser/Add', compact('paymentGateways', 'currencies'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        SmsParser::create($request->validated());

        return redirect()->route('admin.sms-parsers.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SmsParser $smsParser)
    {
        $parserResult = (new Parser())->parserByParser($smsParser->format, $smsParser);
        
        $smsParser->load('paymentGateway');
        $smsParser = SmsParserResource::make($smsParser)->resolve();
        $paymentGateways = PaymentGatewayResource::collection($this->getPaymentGateways())->resolve();

        $currencies = Currency::getAll()
            ->transform(function ($currency) {
                return ['code' => $currency->getCode()];
            });

        return Inertia::render('SmsParser/Edit', compact('smsParser', 'paymentGateways', 'currencies', 'parserResult'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRequest $request, SmsParser $smsParser)
    {
        $smsParser->update($request->validated());

        return redirect()->route('admin.sms-parsers.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SmsParser $smsParser)
    {
        $smsParser->delete();

        return redirect()->route('admin.sms-parsers.index');
    }

    protected function getPaymentGateways(): Collection
    {
        $paymentGateways = queries()->paymentGateway()->getAllActive();

        return $paymentGateways->filter(function (PaymentGateway $paymentGateway) {
            return !$paymentGateway->sub_payment_gateways;
        })->values();
    }
}
