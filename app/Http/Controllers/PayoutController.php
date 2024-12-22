<?php

namespace App\Http\Controllers;

use App\Http\Resources\PayoutGatewayResource;
use App\Models\PayoutGateway;
use Inertia\Inertia;

class PayoutController extends Controller
{
    public function index()
    {
        $payoutGateways = PayoutGateway::query()
            ->where('owner_id', auth()->id())
            ->orderByDesc('id')
            ->paginate(10);
        $payoutGateways = PayoutGatewayResource::collection($payoutGateways);

        return Inertia::render('Payout/Index', compact('payoutGateways'));
    }
}
