<?php

namespace App\Http\Controllers\API\V2;

use App\Enums\OrderStatus;
use App\Enums\OrderSubStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\V2\Order\StoreRequest;
use App\Http\Resources\API\V2\OrderResource;
use App\Models\CascadeDeal;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function show(CascadeDeal $cascadeDeal): JsonResponse
    {
        Gate::authorize('api-access-to-merchant', $cascadeDeal->merchant);

        return response()->success(
            OrderResource::make($cascadeDeal)
        );
    }

    public function store(StoreRequest $request): JsonResponse
    {
        $merchant = queries()->merchant()->findByUUID($request->merchant_id);

        Gate::authorize('api-access-to-merchant', $merchant);

        $cascade_deal = CascadeDeal::create([
            'uuid' => (string) Str::uuid(),
            'external_id' => $request->external_id,
            'merchant_id' => $merchant->id,
            'amount' => $request->amount,
            'initial_amount' => $request->amount,
            'currency' => $request->currency,
            'payment_method' => $request->payment_method,
            'status' => OrderStatus::PENDING,
            'sub_status' => OrderSubStatus::WAITING_FOR_DETAILS_TO_BE_SELECTED,
            'callback_url' => $request->callback_url,
        ]);

        return response()->success(
            OrderResource::make($cascade_deal)
        );
    }
}
