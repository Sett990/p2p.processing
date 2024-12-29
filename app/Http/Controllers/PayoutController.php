<?php

namespace App\Http\Controllers;

use App\Http\Resources\PayoutResource;
use App\Http\Resources\PayoutGatewayResource;
use App\Models\Payout;
use App\Models\PayoutGateway;
use Inertia\Inertia;

class PayoutController extends Controller
{
    public function index()
    {
        $payouts = Payout::query()
            ->where('owner_id', auth()->id())
            ->orderByDesc('id')
            ->paginate(10);
        $payouts = PayoutResource::collection($payouts);

        $payoutGateways = PayoutGateway::query()
            ->where('owner_id', auth()->id())
            ->orderByDesc('id')
            ->paginate(10);
        $payoutGateways = PayoutGatewayResource::collection($payoutGateways);

        return Inertia::render('Payout/Index', compact('payoutGateways', 'payouts'));
    }

    public function show(Payout $payout)
    {
        $payout = PayoutResource::make($payout)->resolve();

        return Inertia::render('Payout/Show', compact('payout'));
    }
}
