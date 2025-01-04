<?php

namespace App\Services\MoneyHolder;

use App\Contracts\FundsHolderServiceContract;
use App\Enums\BalanceType;
use App\Enums\TransactionType;
use App\Exceptions\FundsHolderException;
use App\Models\FundsOnHold;
use App\Models\Payout;
use App\Models\Wallet;
use App\Services\Money\Currency;
use App\Services\Money\Money;
use Carbon\Carbon;

class FundsHolderService implements FundsHolderServiceContract
{
    public function holdFundsFor(
        Money $amount,
        Wallet $sourceWallet,
        Wallet $destinationWallet,
        BalanceType $sourceWalletBalanceType,
        BalanceType $destinationWalletBalanceType,
        Payout $forAction,
        Carbon $until,
    ): FundsOnHold
    {
        if ($amount->getCurrency()->notEquals(Currency::USDT())) {
            throw FundsHolderException::invalidAmountCurrency();
        }

        //TODO validate insufficient funds

        services()->wallet()->takeFormBalance(
            wallet: $sourceWallet,
            amount: $amount,
            transactionType: TransactionType::PAYMENT_FOR_OPENED_PAYOUT,
            balanceType: $sourceWalletBalanceType,
        );

        $fundsOnHold = FundsOnHold::create([
            'amount' => $amount,
            'currency' => $amount->getCurrency(),
            'source_wallet_id' => $sourceWallet->id,
            'source_wallet_balance_type' => $sourceWalletBalanceType,
            'destination_wallet_id' => $destinationWallet->id,
            'destination_wallet_balance_type' => $destinationWalletBalanceType,
            'hold_until' => $until,
        ]);

        $fundsOnHold->holdable()->associate($forAction)->save();

        return $fundsOnHold;
    }
}
