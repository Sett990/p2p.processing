<?php

namespace App\Services\Payout;

use App\Contracts\PayoutServiceContract;
use App\DTO\Payout\PayoutCreateDTO;
use App\Enums\BalanceType;
use App\Enums\MarketEnum;
use App\Enums\PayoutOperationType;
use App\Enums\PayoutStatus;
use App\Enums\TransactionType;
use App\Exceptions\PayoutException;
use App\Models\Merchant;
use App\Models\Payout\Payout;
use App\Models\PaymentGateway;
use App\Models\Wallet;
use App\Services\Money\Currency;
use App\Services\Money\Money;
use App\Utils\Transaction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class PayoutService implements PayoutServiceContract
{
    /**
     * @throws PayoutException
     */
    public function create(PayoutCreateDTO $data): Payout
    {
        return Transaction::run(function () use ($data) {
            $this->ensureGatewaySupportsPayouts($data->paymentGateway);

            $merchantWallet = $this->resolveMerchantWallet($data->merchant);

            $conversionPrice = services()->market()->getBuyPrice(
                $data->amountFiat->getCurrency(),
                MarketEnum::BYBIT
            );

            if (bccomp($conversionPrice->toPrecision(), '0', 8) <= 0) {
                throw PayoutException::marketPriceUnavailable();
            }

            $totalRate = (float) $data->paymentGateway->total_service_commission_rate_for_payouts;
            $traderRate = (float) $data->paymentGateway->trader_commission_rate_for_payouts;
            $teamLeaderRate = 0.0;
            $serviceRate = max($totalRate - $traderRate - $teamLeaderRate, 0);

            $usdtBody = $this->convertToUsdt($data->amountFiat, $conversionPrice);
            $totalFee = $usdtBody->mul($this->rateFraction($totalRate));
            $traderFee = $totalRate > 0
                ? $totalFee->mul($this->rateFraction($traderRate, $totalRate))
                : Money::zero(Currency::USDT()->getCode());
            $teamLeadFee = $totalRate > 0 && $teamLeaderRate > 0
                ? $totalFee->mul($this->rateFraction($teamLeaderRate, $totalRate))
                : Money::zero(Currency::USDT()->getCode());
            $serviceFee = $totalFee->sub($traderFee)->sub($teamLeadFee)->abs();
            $merchantDebit = $usdtBody->add($totalFee);
            $traderCredit = $usdtBody->add($traderFee);
            $available = services()->wallet()->getTotalAvailableBalance($merchantWallet, BalanceType::MERCHANT);

            if ($available->lessThan($merchantDebit)) {
                throw PayoutException::insufficientMerchantFunds();
            }

            $rateFixedAt = Carbon::now();

            $payout = Payout::query()->create([
                'uuid' => (string) Str::uuid(),
                'merchant_id' => $data->merchant->id,
                'payment_gateway_id' => $data->paymentGateway->id,
                'payout_method_type' => $data->methodType,
                'requisites' => $data->requisites,
                'initials' => $data->initials,
                'amount_fiat' => $data->amountFiat,
                'amount_fiat_currency' => strtolower($data->currencyCode),
                'usdt_body' => $usdtBody,
                'total_fee' => $totalFee,
                'trader_fee' => $traderFee,
                'teamlead_fee' => $teamLeadFee,
                'service_fee' => $serviceFee,
                'merchant_debit' => $merchantDebit,
                'trader_credit' => $traderCredit,
                'rate_market' => MarketEnum::BYBIT,
                'conversion_price' => $conversionPrice,
                'conversion_price_currency' => strtoupper($conversionPrice->getCurrency()->getCode()),
                'rate_fixed_at' => $rateFixedAt,
                'status' => PayoutStatus::OPEN,
                'calc_meta' => $this->buildCalcMeta(
                    $data,
                    $conversionPrice,
                    $usdtBody,
                    $totalFee,
                    $merchantDebit,
                    $traderCredit,
                    $totalRate,
                    $traderRate,
                    $teamLeaderRate,
                    $serviceRate,
                    $rateFixedAt
                ),
                'total_commission_rate' => $totalRate,
                'trader_commission_rate' => $traderRate,
                'teamlead_commission_rate' => $teamLeaderRate,
                'service_commission_rate' => $serviceRate,
            ]);

            services()->wallet()->takeFromBalance(
                walletID: $merchantWallet->id,
                amount: $merchantDebit,
                transactionType: TransactionType::PAYMENT_FOR_OPENED_PAYOUT,
                balanceType: BalanceType::MERCHANT
            );

            $this->logOperation($payout, PayoutOperationType::RESERVE_FROM_MERCHANT, $merchantDebit, [
                'wallet_id' => $merchantWallet->id,
            ]);

            return $payout->load('merchant', 'paymentGateway');
        });
    }

    /**
     * @throws PayoutException
     */
    public function cancel(Payout $payout): Payout
    {
        return Transaction::run(function () use ($payout) {
            $payout->refresh()->loadMissing('merchant.user.wallet');

            if ($payout->status->notEquals(PayoutStatus::OPEN)) {
                throw PayoutException::payoutNotOpen();
            }

            if ($payout->trader_id !== null) {
                throw PayoutException::payoutAlreadyTaken();
            }

            $merchantWallet = $this->resolveMerchantWallet($payout->merchant);

            services()->wallet()->giveToBalance(
                walletID: $merchantWallet->id,
                amount: $payout->merchant_debit,
                transactionType: TransactionType::REFUND_FOR_CANCELED_PAYOUT,
                balanceType: BalanceType::MERCHANT
            );

            $payout->update([
                'status' => PayoutStatus::CANCELED,
                'canceled_at' => now(),
            ]);

            $this->logOperation($payout, PayoutOperationType::RETURN_TO_MERCHANT, $payout->merchant_debit, [
                'wallet_id' => $merchantWallet->id,
            ]);

            return $payout->load('merchant', 'paymentGateway');
        });
    }

    private function ensureGatewaySupportsPayouts(PaymentGateway $gateway): void
    {
        if (! (bool) $gateway->is_active) {
            throw PayoutException::gatewayInactive();
        }

        if (! $gateway->is_payouts_enabled) {
            throw PayoutException::gatewayPayoutsDisabled();
        }
    }

    private function resolveMerchantWallet(Merchant $merchant): Wallet
    {
        $merchant->loadMissing('user.wallet');

        if (! $merchant->user) {
            throw PayoutException::merchantWalletMissing();
        }

        if (! $merchant->user->wallet) {
            $wallet = services()->wallet()->create($merchant->user);
            $merchant->user->setRelation('wallet', $wallet);
        }

        return $merchant->user->wallet;
    }

    private function logOperation(Payout $payout, PayoutOperationType $type, ?Money $amount, array $meta = []): void
    {
        $payout->operations()->create([
            'type' => $type,
            'amount' => $amount,
            'amount_currency' => $amount?->getCurrency()->getCode(),
            'meta' => $meta,
        ]);
    }

    private function convertToUsdt(Money $amountFiat, Money $conversionPrice): Money
    {
        if ($amountFiat->getCurrency()->notEquals($conversionPrice->getCurrency())) {
            throw new \InvalidArgumentException('Conversion currencies must match.');
        }

        $usdtAmount = bcdiv(
            $amountFiat->toPrecision(),
            $conversionPrice->toPrecision(),
            Money::DEFAULT_PRECISION
        );

        return Money::fromPrecision($usdtAmount, 'USDT');
    }

    private function rateFraction(float $value, float $divider = 100): string
    {
        if ($divider === 0.0) {
            return '0';
        }

        $fraction = bcdiv((string)$value, (string)$divider, 10);

        return rtrim(rtrim($fraction, '0'), '.') ?: '0';
    }

    private function buildCalcMeta(
        PayoutCreateDTO $data,
        Money $conversionPrice,
        Money $usdtBody,
        Money $totalFee,
        Money $merchantDebit,
        Money $traderCredit,
        float $totalRate,
        float $traderRate,
        float $teamLeaderRate,
        float $serviceRate,
        Carbon $rateFixedAt,
    ): array {
        return [
            'logic' => 'OUT_BODY',
            'inputs' => [
                'amount_fiat' => $data->amountFiat->toPrecision(),
                'amount_currency' => strtoupper($data->currencyCode),
                'total_commission_rate' => $totalRate,
                'trader_commission_rate' => $traderRate,
                'teamlead_commission_rate' => $teamLeaderRate,
                'service_commission_rate' => $serviceRate,
            ],
            'exchange' => [
                'market' => MarketEnum::BYBIT->value,
                'price' => $conversionPrice->toPrecision(),
                'currency' => strtoupper($conversionPrice->getCurrency()->getCode()),
                'fixed_at' => $rateFixedAt->toIso8601String(),
            ],
            'outputs' => [
                'usdt_body' => $usdtBody->toPrecision(),
                'total_fee' => $totalFee->toPrecision(),
                'merchant_debit' => $merchantDebit->toPrecision(),
                'trader_credit' => $traderCredit->toPrecision(),
            ],
        ];
    }
}

