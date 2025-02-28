<?php

namespace App\Http\Controllers\API\H2H;

use App\Enums\NetworkEnum;
use App\Exceptions\DisputeException;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\H2H\Dispute\StoreRequest;
use App\Http\Resources\API\H2H\DisputeResource;
use App\Models\Order;
use App\Services\Money\Currency;
use App\Services\Money\Money;
use Illuminate\Support\Facades\Gate;

class DisputeController extends Controller
{
    public function show(Order $order)
    {
        if (! $order->is_h2h) {
            return response()->failWithMessage('Сделка предназначена не для H2H API, а для Merchant API.');
        }
        if (! $order->dispute) {
            return response()->failWithMessage('По сделке пока что небыло споров.');
        }

        Gate::authorize('access-to-order', $order);

        return response()->success(
            DisputeResource::make($order->dispute)
        );
    }

    public function store(StoreRequest $request, Order $order)
    {
        if (! $order->is_h2h) {
            return response()->failWithMessage('Сделка предназначена не для H2H API, а для Merchant API.');
        }

        Gate::authorize('access-to-order', $order);

        try {
            $dispute = cache()->lock('open-dispute-'.$order->id, 8)
                ->block(10, function () use ($order, $request) {
                    return services()->dispute()->create($order, $request->receipt);
                });

            return response()->success(
                DisputeResource::make($dispute)
            );
        } catch (DisputeException $e) {
            return response()->failWithMessage($e->getMessage());
        }
    }
}
