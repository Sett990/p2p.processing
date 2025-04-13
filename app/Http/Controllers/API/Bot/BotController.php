<?php

namespace App\Http\Controllers\API\Bot;

use App\Exceptions\DisputeException;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\H2H\Dispute\StoreRequest;
use App\Http\Resources\API\H2H\DisputeResource;
use App\Http\Resources\API\H2H\OrderResource;
use App\Http\Resources\PaymentDetailResource;
use App\Http\Resources\UserResource;
use App\Models\Order;
use Illuminate\Http\Request;

class BotController extends Controller
{
    public function index(Order $order)
    {
        $order->load(['paymentDetail', 'dispute.paymentGateway', 'paymentGateway']);

        return response()->success([
            'order' => OrderResource::make($order)->resolve(),
            'detail' => PaymentDetailResource::make($order->paymentDetail)->resolve(),
            'user' => UserResource::make($order->paymentDetail->user)->resolve(),
            'dispute' => $order->dispute ? DisputeResource::make($order->dispute)->resolve() : null,
        ]);
    }

    public function indexExternal(string $merchant_id, string $external_id)
    {
        $order = Order::query()
            ->with(['paymentDetail', 'dispute.paymentGateway', 'paymentGateway'])
            ->whereRelation('merchant', 'uuid', $merchant_id)
            ->where('external_id', $external_id)
            ->firstOrFail();

        return response()->success([
            'order' => OrderResource::make($order)->resolve(),
            'detail' => PaymentDetailResource::make($order->paymentDetail)->resolve(),
            'user' => UserResource::make($order->paymentDetail->user)->resolve(),
            'dispute' => $order->dispute ? DisputeResource::make($order->dispute)->resolve() : null,
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

        try {
            services()->dispute()->accept($order->dispute->id);

            return response()->success();
        } catch (DisputeException $e) {
            return response()->failWithMessage($e->getMessage());
        }
    }

    public function cancelDispute(Request $request, Order $order)
    {
        $request->validate([
            'reason' => ['required', 'string', 'max:255'],
        ]);

        if (! $order->dispute) {
            return response()->failWithMessage('Dispute not found.');
        }

        try {
            services()->dispute()->cancel($order->dispute->id, $request->reason);

            return response()->success();
        } catch (DisputeException $e) {
            return response()->failWithMessage($e->getMessage());
        }
    }
}
