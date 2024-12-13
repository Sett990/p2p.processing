<?php

namespace App\Services\Wallet\TakeFromBalanceHandler;

use App\Enums\BalanceType;
use App\Enums\TransactionDirection;
use App\Enums\TransactionType;
use App\Exceptions\WalletException;
use App\Jobs\SendTelegramNotificationJob;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Services\Money\Money;
use App\Services\TelegramBot\Notifications\LowBalance;

class TakeFromTrust extends TakeFromBalance
{
    public function handle(Wallet $wallet, Money $amount, TransactionType $transactionType): void
    {
        if ($transactionType->direction()->notEquals(TransactionDirection::OUT)) {
            throw WalletException::invalidTransactionTypeForTake();
        }

        $trust = $wallet->trust_balance->sub($amount);

        if ($trust->lessThanZero()) {
            $wallet->update([
                'trust_balance' => 0,
                'reserve_balance' => $wallet->reserve_balance->sub($trust->abs()),
            ]);
        } else {
            $wallet->update([
                'trust_balance' => $trust,
            ]);
        }

        //TODO remove from here.
        if (Wallet::RESERVE_BALANCE / 10 > intval($wallet->trust_balance->toBeauty()) && $wallet->user->telegram) {
            SendTelegramNotificationJob::dispatch(
                new LowBalance(
                    telegram: $wallet->user->telegram,
                    wallet: $wallet
                )
            );
        }

        Transaction::create([
            'amount' => $amount,
            'direction' => TransactionDirection::OUT,
            'type' => $transactionType,
            'balance_type' => BalanceType::TRUST,
            'wallet_id' => $wallet->id,
        ]);
    }
}
