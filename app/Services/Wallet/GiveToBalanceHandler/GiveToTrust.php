<?php

namespace App\Services\Wallet\GiveToBalanceHandler;

use App\Enums\BalanceType;
use App\Enums\TransactionDirection;
use App\Enums\TransactionType;
use App\Exceptions\WalletException;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Services\Money\Money;

class GiveToTrust extends GiveToBalance
{
    public function handle(Wallet $wallet, Money $amount, TransactionType $transactionType): void
    {
        if ($transactionType->direction()->notEquals(TransactionDirection::IN)) {
            throw WalletException::invalidTransactionTypeForGive();
        }

        $reserve = $wallet->reserve_balance
            ->sub(Wallet::RESERVE_BALANCE)
            ->abs();

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
            'direction' => TransactionDirection::IN,
            'type' => $transactionType,
            'balance_type' => BalanceType::TRUST,
            'wallet_id' => $wallet->id,
        ]);
    }
}
