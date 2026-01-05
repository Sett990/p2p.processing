<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Payout\UpdateStatusRequest;
use App\Http\Resources\Payout\AdminPayoutResource;
use App\Models\Payout\Payout;
use App\Models\User;
use App\Enums\PayoutStatus;
use App\Exceptions\PayoutException;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class PayoutController extends Controller
{
    public function index(): Response
    {
        $filters = $this->getTableFilters();
        $filtersVariants = $this->getFiltersData();

        $payouts = queries()->payout()->paginateForAdmin($filters);
        $payouts = AdminPayoutResource::collection($payouts);

        $traders = \App\Models\User::query()
            ->select(['id', 'name', 'email'])
            ->role('Trader')
            ->where('payouts_enabled', true)
            ->where('is_online', true)
            ->orderBy('name')
            ->get();

        return Inertia::render('Payout/Admin/Index', [
            'payouts' => $payouts,
            'filters' => $filters,
            'filtersVariants' => $filtersVariants,
            'traders' => $traders,
        ]);
    }

    public function updateStatus(UpdateStatusRequest $request, Payout $payout): RedirectResponse
    {
        $status = PayoutStatus::from($request->validated('status'));
        $trader = $request->validated('trader_id')
            ? User::query()->find($request->validated('trader_id'))
            : null;

        try {
            services()->payout()->adminChangeStatus(
                payout: $payout,
                status: $status,
                trader: $trader,
                note: $request->validated('note') ?? null,
            );
        } catch (PayoutException $exception) {
            return redirect()->back()->with('error', $exception->getMessage());
        }

        return redirect()->back()->with('message', 'Статус выплаты обновлён.');
    }
}


