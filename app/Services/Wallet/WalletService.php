<?php

namespace App\Services\Wallet;

use App\Contracts\WalletServiceContract;
use App\Enums\BalanceType;
use App\Enums\TransactionType;
use App\Models\User;
use App\Models\Wallet;
use App\Services\Money\Money;
use App\Services\Wallet\GiveToBalanceHandler\GiveToMerchant;
use App\Services\Wallet\GiveToBalanceHandler\GiveToTrust;
use App\Services\Wallet\TakeFromBalanceHandler\TakeFromMerchant;
use App\Services\Wallet\TakeFromBalanceHandler\TakeFromTrust;

class WalletService implements WalletServiceContract
{
    public function getMaxReserveBalance(): int
    {
        return Wallet::RESERVE_BALANCE;
    }

    public function create(User $user): Wallet
    {
        return Wallet::create([
            'merchant_balance' => 0,
            'trust_balance' => 0,
            'reserve_balance' => 0,
            'user_id' => $user->id,
        ]);
    }

    public function takeFormBalance(Wallet $wallet, Money $amount, TransactionType $transactionType, BalanceType $balanceType): void
    {
        $handler = null;

        if ($balanceType->equals(BalanceType::TRUST)) {
            $handler = new TakeFromTrust();
        } else if ($balanceType->equals(BalanceType::MERCHANT)) {
            $handler = new TakeFromMerchant();
        }

        $handler->handle($wallet, $amount, $transactionType);
    }

    public function giveToBalance(Wallet $wallet, Money $amount, TransactionType $transactionType, BalanceType $balanceType): void
    {
        $handler = null;

        if ($balanceType->equals(BalanceType::TRUST)) {
            $handler = new GiveToTrust();
        } else if ($balanceType->equals(BalanceType::MERCHANT)) {
            $handler = new GiveToMerchant();
        }

        $handler->handle($wallet, $amount, $transactionType);
    }

    public function getTotalAvailableBalance(Wallet $wallet, BalanceType $balanceType): Money
    {
        if ($balanceType->equals(BalanceType::TRUST)) {
            $balanceAmount = $wallet->trust_balance->add($wallet->reserve_balance);
        }
        if ($balanceType->equals(BalanceType::MERCHANT)) {
            $balanceAmount = $wallet->merchant_balance;
        }

        return $balanceAmount;
    }
}
