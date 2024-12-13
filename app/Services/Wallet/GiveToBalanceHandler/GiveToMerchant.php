<?php

namespace App\Services\Wallet\GiveToBalanceHandler;

use App\Enums\TransactionType;
use App\Models\Wallet;
use App\Services\Money\Money;

class GiveToMerchant extends GiveToBalance
{
    public function handle(Wallet $wallet, Money $amount, TransactionType $transactionType): void
    {
        //TODO $transactionType
        $balance = $wallet->merchant_balance->add($amount);

        $wallet->update([
            'merchant_balance' => $balance,
        ]);
    }
}
