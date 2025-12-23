<?php

namespace App\Services\Payout\Utils;

use App\DTO\Payout\PayoutCreateDTO;
use App\Enums\BalanceType;
use App\Enums\DetailType;
use App\Enums\PayoutRequisiteType;
use App\Enums\PayoutStatus;
use App\Enums\PayoutSubStatus;
use App\Exceptions\PayoutException;
use App\Models\Payout;
use App\Services\Money\Currency;
use App\Services\Money\Money;
use Illuminate\Support\Str;

class PayoutMaker
{
    public function create(PayoutCreateDTO $dto): Payout
    {
        if (! $dto->payoutGateway->enabled) {
            throw PayoutException::payoutGatewayIsDisabled();
        }

        if (! $dto->paymentGateway->payouts_enabled) {
            throw PayoutException::payoutGatewayIsDisabled();
        }

        $serviceCommission = $dto->paymentGateway->total_service_commission_rate_for_payouts;
        $exchangePriceMarkupRate = $dto->paymentGateway->trader_commission_rate_for_payouts;

        $baseExchangePrice = services()->market()->getBuyPrice($dto->amount->getCurrency());
        $markupPart = $baseExchangePrice->mul($exchangePriceMarkupRate / 100);
        $exchangePrice = $baseExchangePrice->sub($markupPart);

        $baseLiquidityAmount = $dto->amount->convert($exchangePrice, Currency::USDT());

        $serviceCommissionAmount = $baseLiquidityAmount->mul($serviceCommission / 100);
        $exchangeMarkupAmount = $dto->amount
            ->convert($baseExchangePrice, Currency::USDT())
            ->sub($baseLiquidityAmount)
            ->abs();

        $liquidityAmount = $baseLiquidityAmount->add($serviceCommissionAmount);

        $traderProfit = $baseLiquidityAmount;

        $sufficientBalance = $dto->payoutGateway
            ->owner
            ->wallet
            ->merchant_balance
            ->greaterOrEquals($liquidityAmount);

        if (! $sufficientBalance) {
            throw PayoutException::insufficientBalance();
        }

        $payout = Payout::create([
            'uuid' => (string)Str::uuid(),
            'external_id' => $dto->externalId,
            'detail' => $dto->detail,
            'detail_type' => $dto->detailType,
            'requisite_type' => $dto->requisiteType,
            'detail_initials' => $dto->detailInitials,
            'payout_amount' => $dto->amount,
            'currency' => $dto->paymentGateway->currency,
            'base_liquidity_amount' => $baseLiquidityAmount,
            'liquidity_amount' => $liquidityAmount,
            'service_commission_rate' => $serviceCommission,
            'service_commission_amount' => $serviceCommissionAmount,
            'trader_profit_amount' => $traderProfit,
            'trader_exchange_markup_rate' => $exchangePriceMarkupRate,
            'trader_exchange_markup_amount' => $exchangeMarkupAmount,
            'base_exchange_price' => $baseExchangePrice,
            'exchange_price' => $exchangePrice,
            'status' => PayoutStatus::PENDING,
            'sub_status' => PayoutSubStatus::WAITING_FOR_TRADER,
            'callback_url' => $dto->callbackUrl,
            'payout_offer_id' => null,
            'payout_gateway_id' => $dto->payoutGateway->id,
            'payment_gateway_id' => $dto->paymentGateway->id,
            'sub_payment_gateway_id' => $dto->subPaymentGateway?->id,
            'trader_id' => null,
            'owner_id' => $dto->payoutGateway->owner->id,
            'finished_at' => null,
            'expires_at' => null,
        ]);

        services()->fundsHolder()->holdFundsFor(
            amount: $baseLiquidityAmount,
            sourceWallet: $dto->payoutGateway->owner->wallet,
            destinationWallet: null,
            sourceWalletBalanceType: BalanceType::MERCHANT,
            destinationWalletBalanceType: BalanceType::TRUST,
            forAction: $payout,
        );

        services()->fundsHolder()->holdFundsFor(
            amount: $serviceCommissionAmount,
            sourceWallet: $dto->payoutGateway->owner->wallet,
            destinationWallet: User::find(1)->wallet, //TODO
            sourceWalletBalanceType: BalanceType::MERCHANT,
            destinationWalletBalanceType: BalanceType::COMMISSION,
            forAction: $payout,
        );

        return $payout;
    }
}
