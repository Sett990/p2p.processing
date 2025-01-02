<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\PayoutOfferResource;
use App\Http\Resources\PayoutResource;
use App\Http\Resources\PayoutGatewayResource;
use App\Models\Payout;
use App\Models\PayoutGateway;
use App\Models\PayoutOffer;
use Inertia\Inertia;

class PayoutController extends Controller
{
    public function index()
    {
        $problematicPayouts = Payout::query()
            ->with(['previousTrader', 'owner', 'payoutGateway', 'paymentGateway', 'subPaymentGateway'])
            ->whereNull('trader_id')
            ->orderByDesc('id')
            ->get();
        $problematicPayouts = PayoutResource::collection($problematicPayouts);

        $payouts = Payout::query()
            ->with(['trader', 'owner', 'payoutGateway', 'paymentGateway', 'subPaymentGateway'])
            ->whereNotNull('trader_id')
            ->orderByDesc('id')
            ->paginate(10);
        $payouts = PayoutResource::collection($payouts);

        $payoutGateways = PayoutGateway::query()
            ->orderByDesc('id')
            ->paginate(10);
        $payoutGateways = PayoutGatewayResource::collection($payoutGateways);

        $payoutOffers = PayoutOffer::query()
            ->orderByDesc('owner_id')
            ->paginate(10);
        $payoutOffers = PayoutOfferResource::collection($payoutOffers);

        return Inertia::render('Payout/Admin/Index', compact('payoutGateways', 'payouts', 'payoutOffers', 'problematicPayouts'));
    }

    public function receipt(Payout $payout)
    {
        $file_path = storage_path('video_receipts/'.$payout->video_receipt);

        return response()->file($file_path);
    }
}
