<?php

namespace App\Services\Wallet;

use App\Contracts\WalletServiceContract;
use App\Enums\TransactionDirection;
use App\Enums\TransactionType;
use App\Exceptions\WalletException;
use App\Jobs\SendTelegramNotificationJob;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Services\Money\Currency;
use App\Services\Money\Money;
use App\Services\TelegramBot\Notifications\LowBalance;

class WalletService implements WalletServiceContract
{
    const RESERVE_BALANCE = 1000;

    public function getMaxReserveBalance(): int
    {
        return self::RESERVE_BALANCE;
    }

    public function create(User $user): Wallet
    {
        return Wallet::create([
            'merchant_balance' => Money::fromPrecision(0, Currency::USDT()),
            'trust_balance' => Money::fromPrecision(0, Currency::USDT()),
            'reserve_balance' => Money::fromPrecision(0, Currency::USDT()),
            'currency' => Currency::USDT(),
            'user_id' => $user->id,
        ]);
    }

    public function takeMerchant(Wallet $wallet, Money $amount): void
    {
        $balance = $wallet->merchant_balance->sub($amount);

        $wallet->update([
            'merchant_balance' => $balance,
        ]);
    }

    public function giveMerchant(Wallet $wallet, Money $amount): void
    {
        $balance = $wallet->merchant_balance->add($amount);

        $wallet->update([
            'merchant_balance' => $balance,
        ]);
    }

    public function takeTrust(Wallet $wallet, Money $amount, TransactionType $type): void
    {
        if ($type->direction()->notEquals(TransactionDirection::OUT)) {
            throw WalletException::make('Invalid transaction type.');
        }

        $trust = $wallet->trust_balance->sub($amount);

        if ($trust->lessThanZero()) {
            $reserve = $wallet->reserve_balance->sub(abs($trust->toBeauty()));
            $wallet->update([
                'trust_balance' => Money::fromPrecision(0, Currency::USDT()),
                'reserve_balance' => $reserve,
            ]);
        } else {
            $wallet->update([
                'trust_balance' => $trust,
            ]);
        }

        if (self::RESERVE_BALANCE / 10 > intval($wallet->trust_balance->toBeauty()) && $wallet->user->telegram) {
            SendTelegramNotificationJob::dispatch(
                new LowBalance(
                    telegram: $wallet->user->telegram,
                    wallet: $wallet
                )
            );
        }

        Transaction::create([
            'amount' => $amount,
            'currency' => $amount->getCurrency(),
            'direction' => TransactionDirection::OUT,
            'type' => $type,
            'wallet_id' => $wallet->id,
        ]);
    }

    public function giveTrust(Wallet $wallet, Money $amount, TransactionType $type): void
    {
        if ($type->direction()->notEquals(TransactionDirection::IN)) {
            throw WalletException::make('Invalid transaction type.');
        }

        $reserve = $wallet->reserve_balance->sub($this->getMaxReserveBalance());

        if ($reserve->lessThanZero()) {
            $reserve = abs($reserve->toBeauty());
        }

        $trust = $amount->sub($reserve);

        if ($trust->greaterThanZero()) {
            $wallet->update([
                'trust_balance' => $wallet->trust_balance->add($trust),
                'reserve_balance' => $wallet->reserve_balance->add($reserve),
            ]);
        } else {
            $wallet->update([
                'reserve_balance' => $wallet->reserve_balance->add($amount),
            ]);
        }

        Transaction::create([
            'amount' => $amount,
            'currency' => $amount->getCurrency(),
            'direction' => TransactionDirection::IN,
            'type' => $type,
            'wallet_id' => $wallet->id,
        ]);
    }
}
