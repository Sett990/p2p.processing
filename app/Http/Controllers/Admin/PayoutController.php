<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PayoutStatus;
use App\Enums\PayoutSubStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\PayoutOfferResource;
use App\Http\Resources\PayoutResource;
use App\Http\Resources\PayoutGatewayResource;
use App\Models\Payout;
use App\Models\PayoutGateway;
use App\Models\PayoutOffer;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PayoutController extends Controller
{
    public function index()
    {
        $problematicPayouts = Payout::query()
            ->with(['previousTrader', 'owner', 'payoutGateway', 'paymentGateway', 'subPaymentGateway'])
            ->whereNull('trader_id')
            ->orderByDesc('id')
            ->paginate(10);
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

    public function show(Payout $payout)
    {
        $payout->load(['previousTrader', 'owner', 'payoutGateway', 'paymentGateway', 'subPaymentGateway']);

        if ($payout->sub_status->notEquals(PayoutSubStatus::PROCESSING_BY_ADMINISTRATOR)) {
            abort(403);
        }

        $payout = PayoutResource::make($payout)->resolve();

        return Inertia::render('Payout/Admin/Show', compact('payout'));
    }

    public function receipt(Payout $payout)
    {
        $file_path = storage_path('video_receipts/'.$payout->video_receipt);

        return response()->file($file_path);
    }

    public function finish(Payout $payout)
    {
        services()->payout()->finishPayout($payout);

        return redirect()->route('admin.payouts.index')->with('message', 'Вы завершили выплату. Средства поступили на ваш счет.');
    }

    public function cancel(Payout $payout, Request $request)
    {
        $request->validate([
            'reason' => ['nullable', 'string', 'min:10', 'max:1000'],
        ]);

        services()->payout()->cancelPayout($payout, $request->reason);

        return redirect()->route('admin.payouts.index')->with('message', 'Вы отклонили выплату, деньги вернутся на счет мерчанта.');
    }

    public function passToTrader(Payout $payout)
    {
        services()->payout()->passToTrader($payout);

        return redirect()->route('admin.payouts.index')->with('message', 'Выплата передана свободному трейдеру. Теперь она отображается в списке всех выплат.');
    }
}
