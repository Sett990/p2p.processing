<?php

namespace App\Http\Controllers;

use App\Enums\PayoutStatus;
use App\Enums\PayoutSubStatus;
use App\Http\Resources\PayoutResource;
use App\Http\Resources\PayoutGatewayResource;
use App\Models\Payout;
use App\Models\PayoutGateway;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
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
        if ($payout->status->notEquals(PayoutStatus::PENDING)) {
            abort(403);
        }
        if ($payout->sub_status->notEquals(PayoutSubStatus::PROCESSING_BY_TRADER)) {
            abort(403);
        }

        $payout = PayoutResource::make($payout)->resolve();

        return Inertia::render('Payout/Show', compact('payout'));
    }

    public function finish(Payout $payout, Request $request)
    {
        services()->payout()->finishPayout($payout);
    }

    public function refuse(Payout $payout, Request $request)
    {
        $request->validate([
            'reason' => ['required', 'string', 'min:10', 'max:255'],
        ]);

        services()->payout()->refusePayout($payout, $request->reason);
    }
}
