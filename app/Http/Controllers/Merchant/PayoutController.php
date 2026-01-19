<?php

namespace App\Http\Controllers\Merchant;

use App\DTO\Payout\PayoutCreateDTO;
use App\Enums\PayoutMethodType;
use App\Exceptions\PayoutException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Payout\StoreRequest;
use App\Http\Resources\Payout\MerchantPayoutResource;
use App\Http\Resources\PaymentGatewayResource;
use App\Models\Merchant;
use App\Models\PaymentGateway;
use App\Services\Money\Money;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class PayoutController extends Controller
{
    public function index(Request $request): Response
    {
        if (! $request->user()->payouts_enabled) {
            abort(403, 'Выплаты для вашего аккаунта отключены.');
        }

        $filters = $this->getTableFilters();
        $filtersVariants = $this->getFiltersData();

        $payouts = queries()->payout()->paginateForMerchant($request->user(), $filters);
        $payouts = MerchantPayoutResource::collection($payouts);

        return Inertia::render('Payout/Merchant/Index', [
            'payouts' => $payouts,
            'filters' => $filters,
            'filtersVariants' => $filtersVariants,
        ]);
    }

    public function createData(Request $request): JsonResponse
    {
        $this->ensurePayoutsEnabled($request);

        $paymentGateways = PaymentGatewayResource::collection(
            PaymentGateway::query()
                ->active()
                ->where('is_payouts_enabled', true)
                ->orderByDesc('id')
                ->get()
        )->resolve();

        $merchants = Merchant::query()
            ->where('user_id', $request->user()->id)
            ->whereNotNull('validated_at')
            ->whereNull('banned_at')
            ->where('active', true)
            ->orderByDesc('id')
            ->get()
            ->transform(function (Merchant $merchant) {
                return [
                    'id' => $merchant->id,
                    'name' => $merchant->name,
                    'payout_callback_url' => $merchant->payout_callback_url,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'paymentGateways' => $paymentGateways,
                'merchants' => $merchants,
                'payoutMethodTypes' => [
                    ['id' => PayoutMethodType::SBP->value, 'name' => 'СБП'],
                    ['id' => PayoutMethodType::CARD->value, 'name' => 'Карта'],
                ],
            ],
        ]);
    }

    public function store(StoreRequest $request): JsonResponse
    {
        $this->ensurePayoutsEnabled($request);

        $merchant = Merchant::query()->findOrFail($request->validated('merchant_id'));

        Gate::authorize('access-to-merchant', $merchant);

        $paymentGateway = PaymentGateway::query()
            ->whereKey($request->validated('payment_method_id'))
            ->where('is_payouts_enabled', true)
            ->active()
            ->firstOrFail();

        $gatewayCurrency = strtoupper($paymentGateway->currency->getCode());

        $dto = PayoutCreateDTO::make(
            merchant: $merchant,
            paymentGateway: $paymentGateway,
            externalId: $request->validated('external_id'),
            amountFiat: Money::fromPrecision($request->validated('amount'), $gatewayCurrency),
            methodType: PayoutMethodType::from($request->validated('payout_method_type')),
            requisites: $request->validated('requisites'),
            initials: $request->validated('initials'),
            currencyCode: $gatewayCurrency,
            callbackUrl: $request->validated('callback_url'),
        );

        try {
            $payout = services()->payout()->create($dto);
        } catch (PayoutException $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'data' => MerchantPayoutResource::make($payout),
        ]);
    }

    private function ensurePayoutsEnabled(Request $request): void
    {
        if (! $request->user()->payouts_enabled) {
            abort(403, 'Выплаты для вашего аккаунта отключены.');
        }
    }
}

