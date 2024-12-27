<?php

namespace App\Http\Controllers\API\Payout;

use App\DTO\Payout\PayoutCreateDTO;
use App\Exceptions\PayoutException;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\Payout\StoreRequest;
use App\Http\Resources\API\PayoutResource;
use Illuminate\Http\Request;

class PayoutController extends Controller
{
    public function store(StoreRequest $request)
    {
        //TODO access to gateway

        try {
            $payout = services()->payout()->createPayout(
                PayoutCreateDTO::makeFromRequest($request->validated())
            );

            return response()->success(
                PayoutResource::make($payout)
            );
        } catch (PayoutException $e) {
            return response()->failWithMessage($e->getMessage());
        }
    }
}
