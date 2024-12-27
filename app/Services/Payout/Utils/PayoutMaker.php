<?php

namespace App\Services\Payout\Utils;

use App\DTO\Payout\PayoutCreateDTO;
use App\Enums\DetailType;
use App\Enums\OrderStatus;
use App\Enums\PayoutStatus;
use App\Enums\PayoutSubStatus;
use App\Exceptions\PayoutException;
use App\Models\PaymentDetail;
use App\Models\PaymentGateway;
use App\Models\Payout;
use App\Models\PayoutOffer;
use App\Services\Money\Currency;
use App\Services\Money\Money;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class PayoutMaker
{
    public function create(PayoutCreateDTO $dto): Payout
    {
        $payoutOffer = $this->getPayoutOffer($dto->amount, $dto->detailType, $dto->paymentGateway);

        if (! $payoutOffer) {
            throw new PayoutException('Payout offer not found.');
        }

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

        return Payout::create([
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
            'payout_gateway_id' => $dto->paymentGateway->id,
            'trader_id' => $payoutOffer->owner->id,
            'owner_id' => $dto->payoutGateway->owner->id,
            'finished_at' => null,
            'expires_at' => $this->getExpirationTime(),
        ]);
    }

    private function getPayoutOffer(Money $amount, DetailType $detailType, PaymentGateway $paymentGateway): ?PayoutOffer
    {
        /**
         * @var PayoutOffer $payoutOffer
         */
        $payoutOffer = PayoutOffer::query()
            ->where('occupied', false)
            ->where('active', true)
            ->get()
            ->filter(function (PayoutOffer $payoutOffer) use ($amount, $detailType, $paymentGateway) {
                return $payoutOffer->currency->getCode() === $amount->getCurrency()->getCode()
                    && $payoutOffer->min_amount->lessOrEquals($amount)
                    && $payoutOffer->max_amount->greaterOrEquals($amount)
                    && $payoutOffer->payment_gateway_id === $paymentGateway->id
                    && $payoutOffer->detail_types->first()->equals($detailType);
            })->random();

        PayoutOffer::query()
            ->where('owner_id', $payoutOffer->owner_id)
            ->update([
                'occupied' => true,
            ]);

        return $payoutOffer;
    }

    protected function getExpirationTime(): Carbon
    {
        return now()->addMinutes(20);
    }
}
