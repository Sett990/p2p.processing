<?php

namespace App\Http\Controllers;

use App\Enums\PayoutStatus;
use App\Enums\PayoutSubStatus;
use App\Http\Resources\PayoutOfferResource;
use App\Http\Resources\PayoutResource;
use App\Models\Payout;
use App\Models\PayoutOffer;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TraderPayoutController extends Controller
{
    public function index()
    {
        $payouts = Payout::query()
            ->with(['trader', 'owner', 'payoutGateway', 'paymentGateway', 'subPaymentGateway', 'fundsOnHold'])
            ->where('trader_id', auth()->id())
            ->orderByDesc('id')
            ->paginate(10);
        $payouts = PayoutResource::collection($payouts);

        $payoutOffers = PayoutOffer::query()
            ->where('owner_id', auth()->id())
            ->orderByDesc('id')
            ->paginate(10);
        $payoutOffers = PayoutOfferResource::collection($payoutOffers);

        return Inertia::render('Payout/Trader/Index', compact('payoutOffers', 'payouts'));
    }

    public function show(Payout $payout)
    {
        $payout->load(['trader', 'owner', 'payoutGateway', 'paymentGateway', 'subPaymentGateway']);

        if ($payout->status->notEquals(PayoutStatus::PENDING)) {
            abort(403);
        }
        if ($payout->sub_status->notEquals(PayoutSubStatus::PROCESSING_BY_TRADER)) {
            abort(403);
        }

        $payout = PayoutResource::make($payout)->resolve();

        return Inertia::render('Payout/Trader/Show', compact('payout'));
    }

    public function finish(Payout $payout, Request $request)
    {
        $request->validate([
            'video_receipt' => ['required', 'mimetypes:video/avi,video/mpeg,video/quicktime', 'max:2048'],
        ]);

        $receiptVideo = $request->file('video_receipt');

        services()->payout()->finishPayout($payout, $receiptVideo);

        return redirect()->route('trader.payouts.index')->with('message', 'Вы завершили выплату. Средства поступят на ваш счет после завершения холда.');
    }

    public function refuse(Payout $payout, Request $request)
    {
        $request->validate([
            'reason' => ['required', 'string', 'min:10', 'max:1000'],
        ]);

        services()->payout()->refusePayout($payout, $request->reason);

        return redirect()->route('trader.payouts.index')->with('message', 'Вы отказались от исполнения выплаты, вы больше не видите ее в списке ваших выплат.');
    }
}
