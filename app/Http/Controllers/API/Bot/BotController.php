<?php

namespace App\Http\Controllers\API\Bot;

use App\Exceptions\DisputeException;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\H2H\Dispute\StoreRequest;
use App\Http\Resources\API\H2H\DisputeResource;
use App\Http\Resources\API\H2H\OrderResource;
use App\Http\Resources\UserResource;
use App\Models\Order;
use Illuminate\Http\Request;

class BotController extends Controller
{
    public function index(string $key)
    {
        $order = Order::where('uuid', $key)
            ->orWhere('external_id', $key)
            ->firstOrFail();

        return response()->success([
            'order' => OrderResource::make($order)->resolve(),
            'detail' => UserResource::make($order->paymentDetail)->resolve(),
            'user' => UserResource::make($order->paymentDetail->user)->resolve(),
        ]);
    }

    public function storeDispute(StoreRequest $request, Order $order)
    {
        try {
            $dispute = services()->dispute()->create($order->id, $request->receipt);

            return response()->success(
                DisputeResource::make($dispute)
            );
        } catch (DisputeException $e) {
            return response()->failWithMessage($e->getMessage());
        }
    }

    public function acceptDispute(Order $order)
    {
        if (! $order->dispute) {
            return response()->failWithMessage('Dispute not found.');
        }

        services()->dispute()->accept($order->dispute->id);

        return response()->success();
    }

    public function cancelDispute(Request $request, Order $order)
    {
        $request->validate([
            'reason' => ['required', 'string', 'max:255'],
        ]);

        if (! $order->dispute) {
            return response()->failWithMessage('Dispute not found.');
        }

        services()->dispute()->cancel($order->dispute->id, $request->reason);

        return response()->success();
    }
}
