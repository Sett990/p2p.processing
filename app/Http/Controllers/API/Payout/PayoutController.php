<?php

namespace App\Http\Controllers\API\Payout;

use App\DTO\Payout\PayoutCreateDTO;
use App\Exceptions\PayoutException;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\Payout\StoreRequest;
use App\Http\Resources\API\PayoutResource;
use App\Models\Payout;
use Illuminate\Http\Request;

class PayoutController extends Controller
{
    public function __construct()
    {
        //TODO
        if (! auth()->user()->payouts_enabled) {
            abort(403);
        }
    }

    public function show(Payout $payout)
    {
        $payout->load(['trader', 'owner', 'payoutGateway', 'paymentGateway', 'subPaymentGateway']);
        //TODO access to gateway

        return response()->success(
            PayoutResource::make($payout)
        );
    }

    public function store(StoreRequest $request)
    {
        //TODO access to gateway

        try {
            $payout = services()->payout()->createPayout(
                PayoutCreateDTO::makeFromRequest($request->validated())
            );

            $payout->load(['trader', 'owner', 'payoutGateway', 'paymentGateway', 'subPaymentGateway']);

            return response()->success(
                PayoutResource::make($payout)
            );
        } catch (PayoutException $e) {
            return response()->failWithMessage($e->getMessage());
        }
    }
}
