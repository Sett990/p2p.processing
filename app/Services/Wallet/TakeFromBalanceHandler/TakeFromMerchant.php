<?php

namespace App\Services\Wallet\TakeFromBalanceHandler;

use App\Enums\TransactionType;
use App\Models\Wallet;
use App\Services\Money\Money;

class TakeFromMerchant extends TakeFromBalance
{
    public function handle(Wallet $wallet, Money $amount, TransactionType $transactionType): void
    {
        //TODO $transactionType
        $balance = $wallet->merchant_balance->sub($amount);

        $wallet->update([
            'merchant_balance' => $balance,
        ]);
    }
}
