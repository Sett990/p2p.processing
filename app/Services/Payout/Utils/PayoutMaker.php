<?php

namespace App\Services\Payout\Utils;

use App\DTO\Payout\PayoutCreateDTO;
use App\Enums\DetailType;
use App\Enums\PayoutStatus;
use App\Enums\PayoutSubStatus;
use App\Enums\TransactionType;
use App\Exceptions\PayoutException;
use App\Jobs\AutoRefusePayoutJob;
use App\Models\PaymentGateway;
use App\Models\Payout;
use App\Models\PayoutOffer;
use App\Services\Money\Currency;
use App\Services\Money\Money;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class PayoutMaker
{
    public function create(PayoutCreateDTO $dto): Payout
    {
        $serviceCommission = services()->commission()->getPayoutServiceCommissionRate($dto->paymentGateway, $dto->payoutGateway);
        $exchangePriceMarkupRate = services()->commission()->getSellPriceMarkupRate($dto->paymentGateway);

        $baseExchangePrice = services()->market()->getSellPrice($dto->amount->getCurrency());
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

        $payoutOffer = $this->getPayoutOffer($dto->amount, $dto->detailType, $dto->paymentGateway);

        if (! $payoutOffer) {
            throw PayoutException::offerNotExists();
        }

        $dto->payoutGateway->owner->wallet->takeFromMerchant(
            amount: $liquidityAmount,
            type: TransactionType::PAYMENT_FOR_OPENED_PAYOUT
        );

        $expires_at = $this->getExpirationTime();

        $payout = Payout::create([
            'uuid' => (string)Str::uuid(),
            'external_id' => $dto->externalId,
            'detail' => $dto->detail,
            'detail_type' => $dto->detailType,
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
            'sub_status' => PayoutSubStatus::PROCESSING_BY_TRADER,
            'callback_url' => $dto->callbackUrl,
            'payout_offer_id' => $payoutOffer->id,
            'payout_gateway_id' => $dto->payoutGateway->id,
            'payment_gateway_id' => $dto->paymentGateway->id,
            'sub_payment_gateway_id' => $dto->subPaymentGateway?->id,
            'trader_id' => $payoutOffer->owner->id,
            'owner_id' => $dto->payoutGateway->owner->id,
            'finished_at' => null,
            'expires_at' => $expires_at, //TODO возможно убрать
        ]);

        AutoRefusePayoutJob::dispatch($payout, $payoutOffer->owner)->delay($expires_at);

        return $payout;
    }

    private function getPayoutOffer(Money $amount, DetailType $detailType, PaymentGateway $paymentGateway): ?PayoutOffer
    {
        $payoutOffers = PayoutOffer::query()
            ->whereRelation('owner', 'is_payout_online', true)
            ->where('occupied', false)
            ->where('active', true)
            ->get();

        $payoutOffer = null;

        if ($payoutOffers->isNotEmpty()) {
            /**
             * @var PayoutOffer $payoutOffer
             */
            $payoutOffers = $payoutOffers
                ->filter(function (PayoutOffer $payoutOffer) use ($amount, $detailType, $paymentGateway) {
                    return $payoutOffer->currency->getCode() === $amount->getCurrency()->getCode()
                        && $payoutOffer->min_amount->lessOrEquals($amount)
                        && $payoutOffer->max_amount->greaterOrEquals($amount)
                        && $payoutOffer->payment_gateway_id === $paymentGateway->id
                        && $payoutOffer->detail_types->first()->equals($detailType);
                });

            if ($payoutOffers->isNotEmpty()) {
                $payoutOffer = $payoutOffers->random();

                PayoutOffer::query()
                    ->where('owner_id', $payoutOffer->owner_id)
                    ->update([
                        'occupied' => true,
                    ]);
            }
        }

        return $payoutOffer;
    }

    protected function getExpirationTime(): Carbon
    {
        return now()->addMinutes(20);
    }
}
