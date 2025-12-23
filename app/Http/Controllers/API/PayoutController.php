<?php

namespace App\Http\Controllers\API;

use App\DTO\Payout\PayoutCreateDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\Payout\StoreRequest;
use App\Http\Resources\PayoutResource;
use Illuminate\Http\JsonResponse;

class PayoutController extends Controller
{
    public function store(StoreRequest $request): JsonResponse
    {
        $dto = PayoutCreateDTO::makeFromRequest($request->validated());

        $payout = services()->payout()->createPayout($dto);

        return response()->json([
            'data' => PayoutResource::make($payout->fresh([
                'payoutGateway',
                'paymentGateway',
                'subPaymentGateway',
                'owner',
            ]))->resolve(),
        ], 201);
    }
}

