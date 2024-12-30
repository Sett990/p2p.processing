<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
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
        $payout_gateways = request()->input('filters.payout_gateways', '');
        $payout_gateways = explode(',', $payout_gateways);
        $payout_gateways = array_filter($payout_gateways, function ($payout_gateway) {
            return $payout_gateway !== '' && $payout_gateway !== null;
        });
        $statuses = request()->input('filters.statuses', '');
        $statuses = explode(',', $statuses);
        foreach ($statuses as $key => $value) {
            if (! PayoutStatus::tryFrom($value)) {
                unset($statuses[$key]);
            }
        }

        $payoutStatuses = [];
        foreach (PayoutStatus::values() as $status) {
            $payoutStatuses[] = [
                'name' => trans("payout.status.{$status}"),
                'value' => $status,
            ];
        }

        $filtersData = [
            'payout_gateways' => PayoutGateway::query()
                ->where('owner_id', auth()->id())
                ->get()
                ->transform(function (PayoutGateway $gateway) {
                    return [
                        'id' => $gateway->id,
                        'name' => $gateway->name,
                    ];
                }),
            'payout_statuses' => $payoutStatuses,
        ];

        $currentFilters = [
            'payout_gateways' => $payout_gateways,
            'payout_statuses' => $statuses,
        ];

        $payouts = Payout::query()
            ->where('owner_id', auth()->id())
            ->when(! empty($payout_gateways), function ($query) use ($payout_gateways) {
                $query->whereIn('payout_gateway_id', $payout_gateways);
            })
            ->when(! empty($statuses), function ($query) use ($statuses) {
                $query->whereIn('status', $statuses);
            })
            ->orderByDesc('id')
            ->paginate(10);
        $payouts = PayoutResource::collection($payouts);

        $payoutGateways = PayoutGateway::query()
            ->where('owner_id', auth()->id())
            ->orderByDesc('id')
            ->paginate(10);
        $payoutGateways = PayoutGatewayResource::collection($payoutGateways);

        return Inertia::render('Payout/Index', compact('payoutGateways', 'payouts', 'filtersData', 'currentFilters'));
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
