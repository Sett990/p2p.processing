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
use App\Models\User;
use App\Models\Wallet;
use App\Jobs\CreditPayoutToTraderJob;
use App\Jobs\ExpiresPayoutJob;
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

            // Время истечения заявки (протухания) согласно настройке gateway
            $expiresAt = null;
            $reservationMinutes = (int) ($data->paymentGateway->reservation_time_for_payouts ?? 30);
            if ($reservationMinutes > 0) {
                $expiresAt = now()->addMinutes($reservationMinutes);
            }

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
                'expires_at' => $expiresAt,
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

            if ($expiresAt) {
                ExpiresPayoutJob::dispatch($payout)->delay($expiresAt);
            }

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

    /**
     * @throws PayoutException
     */
    public function take(Payout $payout, User $trader): Payout
    {
        return Transaction::run(function () use ($payout, $trader) {
            $payout = Payout::query()
                ->whereKey($payout->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($payout->status->notEquals(PayoutStatus::OPEN)) {
                throw PayoutException::payoutUnavailableForTaking();
            }

            $lockedTrader = User::query()
                ->whereKey($trader->id)
                ->lockForUpdate()
                ->firstOrFail();

            $limit = max((int) $lockedTrader->payout_active_payouts_limit ?: 1, 1);

            $activeCount = Payout::query()
                ->where('trader_id', $lockedTrader->id)
                ->whereIn('status', [
                    PayoutStatus::TAKEN->value,
                    PayoutStatus::SENT->value,
                ])
                ->lockForUpdate()
                ->count();

            if ($activeCount >= $limit) {
                throw PayoutException::traderActiveLimitReached($limit);
            }

            $payout->update([
                'trader_id' => $lockedTrader->id,
                'status' => PayoutStatus::TAKEN,
                'taken_at' => now(),
            ]);

            $this->logOperation($payout, PayoutOperationType::MARK_TAKEN, null, [
                'trader_id' => $lockedTrader->id,
            ]);

            return $payout->load('merchant', 'paymentGateway', 'trader');
        });
    }

    /**
     * @throws PayoutException
     */
    public function markSent(Payout $payout, User $trader): Payout
    {
        return Transaction::run(function () use ($payout, $trader) {
            $payout = Payout::query()
                ->whereKey($payout->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($payout->trader_id !== $trader->id) {
                throw PayoutException::payoutNotAssignedToTrader();
            }

            if ($payout->status->equals(PayoutStatus::COMPLETED)) {
                throw PayoutException::payoutAlreadyCompleted();
            }

            if ($payout->status->equals(PayoutStatus::SENT)) {
                throw PayoutException::payoutAlreadyMarkedAsSent();
            }

            if ($payout->status->notEquals(PayoutStatus::TAKEN)) {
                throw PayoutException::invalidPayoutStatus();
            }

            $now = now();
            $holdMinutes = max((int) ($trader->payout_hold_minutes ?? 60), 1);
            $holdUntil = $trader->payout_hold_enabled
                ? $now->copy()->addMinutes($holdMinutes)
                : null;

            $updatePayload = [
                'status' => PayoutStatus::SENT,
                'sent_at' => $now,
                'hold_until' => $holdUntil,
            ];

            if (! $trader->payout_hold_enabled) {
                $this->completeAndCredit($payout);

                return $payout->fresh()->load('merchant', 'paymentGateway', 'trader');
            }

            $payout->update($updatePayload);

            $this->logOperation($payout, PayoutOperationType::MARK_SENT, $payout->trader_credit, [
                'hold_enabled' => (bool) $trader->payout_hold_enabled,
                'hold_minutes' => $trader->payout_hold_enabled ? $holdMinutes : null,
                'hold_until' => $updatePayload['hold_until']?->toIso8601String(),
            ]);

            if ($trader->payout_hold_enabled && $holdUntil) {
                $this->logOperation($payout, PayoutOperationType::SET_HOLD, $payout->trader_credit, [
                    'hold_until' => $holdUntil->toIso8601String(),
                ]);

                CreditPayoutToTraderJob::dispatch($payout)->delay($holdUntil);
            }

            return $payout->load('merchant', 'paymentGateway', 'trader');
        });
    }

    /**
     * Завершить выплату и зачислить средства трейдеру.
     *
     * @throws PayoutException
     */
    public function completeAndCredit(Payout $payout): void
    {
        Transaction::run(function () use ($payout) {
            $payout->refresh()->loadMissing('trader.wallet');

            if (! $payout->trader || ! $payout->trader->wallet) {
                throw PayoutException::payoutNotAssignedToTrader();
            }

            if ($payout->status->equals(PayoutStatus::COMPLETED)) {
                return;
            }

            $payout->update([
                'status' => PayoutStatus::COMPLETED,
                'completed_at' => now(),
                'hold_until' => $payout->hold_until ?? now(),
            ]);

            services()->wallet()->giveToBalance(
                walletID: $payout->trader->wallet->id,
                amount: $payout->trader_credit,
                transactionType: TransactionType::INCOME_FROM_SUCCESSFUL_PAYOUT,
                balanceType: BalanceType::TRUST
            );

            $this->logOperation($payout, PayoutOperationType::CREDIT_TRADER, $payout->trader_credit);
        });
    }

    /**
     * Ручное изменение статуса администратором. Стараемся привести все побочные
     * эффекты (резервы, холды, начисления) к консистентному состоянию.
     *
     * @throws PayoutException
     */
    public function adminChangeStatus(Payout $payout, PayoutStatus $status, ?User $trader = null, ?string $note = null): Payout
    {
        return Transaction::run(function () use ($payout, $status, $trader, $note) {
            $locked = Payout::query()
                ->whereKey($payout->id)
                ->with(['merchant.user.wallet', 'trader.wallet', 'paymentGateway'])
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->status->equals($status)) {
                return $locked;
            }

            $requiresTrader = in_array($status, [
                PayoutStatus::TAKEN,
                PayoutStatus::SENT,
                PayoutStatus::COMPLETED,
            ], true);

            if ($requiresTrader && ! $trader) {
                throw new PayoutException('Для выбранного статуса необходимо выбрать трейдера.');
            }

            if ($trader) {
                $locked->setRelation('trader', $trader);
            }

            if ($trader && ! $trader->wallet) {
                $trader->setRelation('wallet', services()->wallet()->create($trader));
            }

            $merchantWallet = $this->resolveMerchantWallet($locked->merchant);

            if ($locked->status->equals(PayoutStatus::COMPLETED) && ! $status->equals(PayoutStatus::COMPLETED)) {
                $this->rollbackTraderCredit($locked);
            }

            if ($status->equals(PayoutStatus::CANCELED)) {
                if (! $locked->status->equals(PayoutStatus::CANCELED)) {
                    $this->refundMerchant($locked, $merchantWallet);
                    $this->logOperation(
                        $locked,
                        PayoutOperationType::RETURN_TO_MERCHANT,
                        $locked->merchant_debit,
                        [
                            'manual' => true,
                            'note' => $note,
                        ]
                    );
                }

                $locked->update([
                    'status' => PayoutStatus::CANCELED,
                    'canceled_at' => now(),
                    'trader_id' => null,
                    'taken_at' => null,
                    'sent_at' => null,
                    'hold_until' => null,
                    'completed_at' => null,
                    'expires_at' => null,
                ]);

                return $locked->fresh('merchant', 'paymentGateway', 'trader');
            }

            if ($locked->status->equals(PayoutStatus::CANCELED)) {
                $this->reserveMerchantFunds($locked, $merchantWallet);
                $this->logOperation(
                    $locked,
                    PayoutOperationType::RESERVE_FROM_MERCHANT,
                    $locked->merchant_debit,
                    [
                        'manual' => true,
                    ]
                );
            }

            if ($status->equals(PayoutStatus::OPEN)) {
                if (! $locked->status->equals(PayoutStatus::OPEN) && ! $locked->status->equals(PayoutStatus::CANCELED)) {
                    throw new PayoutException('В этот статус можно перейти только из отменённой или открытой выплаты.');
                }
                $expiresAt = $this->calculateExpiresAt($locked);

                $locked->update([
                    'status' => PayoutStatus::OPEN,
                    'trader_id' => null,
                    'taken_at' => null,
                    'sent_at' => null,
                    'hold_until' => null,
                    'completed_at' => null,
                    'canceled_at' => null,
                    'expires_at' => $expiresAt,
                ]);

                if ($expiresAt) {
                    ExpiresPayoutJob::dispatch($locked)->delay($expiresAt);
                }

                return $locked->fresh('merchant', 'paymentGateway', 'trader');
            }

            if ($status->equals(PayoutStatus::TAKEN)) {
                $locked->update([
                    'status' => PayoutStatus::TAKEN,
                    'trader_id' => $trader?->id,
                    'taken_at' => now(),
                    'sent_at' => null,
                    'hold_until' => null,
                    'completed_at' => null,
                    'canceled_at' => null,
                ]);

                $this->logOperation(
                    $locked,
                    PayoutOperationType::MARK_TAKEN,
                    null,
                    [
                        'manual' => true,
                        'trader_id' => $trader?->id,
                        'note' => $note,
                    ]
                );

                return $locked->fresh('merchant', 'paymentGateway', 'trader');
            }

            if ($status->equals(PayoutStatus::SENT)) {
                $holdMinutes = max((int) ($trader?->payout_hold_minutes ?? 60), 1);
                $holdEnabled = (bool) ($trader?->payout_hold_enabled ?? false);
                $holdUntil = $holdEnabled ? now()->addMinutes($holdMinutes) : null;

                $locked->update([
                    'status' => PayoutStatus::SENT,
                    'trader_id' => $trader?->id,
                    'taken_at' => $locked->taken_at ?? now(),
                    'sent_at' => now(),
                    'hold_until' => $holdUntil,
                    'completed_at' => null,
                    'canceled_at' => null,
                ]);

                $this->logOperation(
                    $locked,
                    PayoutOperationType::MARK_SENT,
                    $locked->trader_credit,
                    [
                        'manual' => true,
                        'hold_enabled' => $holdEnabled,
                        'hold_minutes' => $holdEnabled ? $holdMinutes : null,
                        'hold_until' => $holdUntil?->toIso8601String(),
                        'note' => $note,
                    ]
                );

                if ($holdEnabled && $holdUntil) {
                    $this->logOperation(
                        $locked,
                        PayoutOperationType::SET_HOLD,
                        $locked->trader_credit,
                        [
                            'hold_until' => $holdUntil->toIso8601String(),
                            'manual' => true,
                        ]
                    );

                    CreditPayoutToTraderJob::dispatch($locked)->delay($holdUntil);
                } else {
                    $this->completeAndCredit($locked);
                }

                return $locked->fresh('merchant', 'paymentGateway', 'trader');
            }

            if ($status->equals(PayoutStatus::COMPLETED)) {
                $locked->update([
                    'trader_id' => $trader?->id,
                    'taken_at' => $locked->taken_at ?? now(),
                    'sent_at' => $locked->sent_at ?? now(),
                    'hold_until' => $locked->hold_until ?? now(),
                    'canceled_at' => null,
                ]);

                $this->completeAndCredit($locked);

                return $locked->fresh('merchant', 'paymentGateway', 'trader');
            }

            throw new PayoutException('Не удалось сменить статус выплаты.');
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

    private function reserveMerchantFunds(Payout $payout, Wallet $wallet): void
    {
        $available = services()->wallet()->getTotalAvailableBalance($wallet, BalanceType::MERCHANT);

        if ($available->lessThan($payout->merchant_debit)) {
            throw PayoutException::insufficientMerchantFunds();
        }

        services()->wallet()->takeFromBalance(
            walletID: $wallet->id,
            amount: $payout->merchant_debit,
            transactionType: TransactionType::PAYMENT_FOR_OPENED_PAYOUT,
            balanceType: BalanceType::MERCHANT
        );
    }

    private function refundMerchant(Payout $payout, Wallet $wallet): void
    {
        services()->wallet()->giveToBalance(
            walletID: $wallet->id,
            amount: $payout->merchant_debit,
            transactionType: TransactionType::REFUND_FOR_CANCELED_PAYOUT,
            balanceType: BalanceType::MERCHANT
        );
    }

    private function rollbackTraderCredit(Payout $payout): void
    {
        if (! $payout->trader) {
            return;
        }

        $wallet = $payout->trader->wallet ?? services()->wallet()->create($payout->trader);

        services()->wallet()->takeFromBalance(
            walletID: $wallet->id,
            amount: $payout->trader_credit,
            transactionType: TransactionType::ROLLBACK_INCOME_FROM_SUCCESSFUL_PAYOUT,
            balanceType: BalanceType::TRUST
        );

        $this->logOperation(
            $payout,
            PayoutOperationType::CREDIT_TRADER,
            $payout->trader_credit,
            [
                'manual' => true,
                'rollback' => true,
            ]
        );
    }

    private function calculateExpiresAt(Payout $payout): ?Carbon
    {
        $reservationMinutes = (int) ($payout->paymentGateway?->reservation_time_for_payouts ?? 30);

        if ($reservationMinutes <= 0) {
            return null;
        }

        return now()->addMinutes($reservationMinutes);
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

