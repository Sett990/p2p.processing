<?php

namespace App\Contracts;

use App\Enums\BalanceType;
use App\Enums\TransactionType;
use App\Models\User;
use App\Models\Wallet;
use App\Services\Money\Money;

interface WalletServiceContract
{
    public function getMaxReserveBalance(): int;

    public function create(User $user): Wallet;

    public function takeFormBalance(Wallet $wallet, Money $amount, TransactionType $transactionType, BalanceType $balanceType): void;

    public function giveToBalance(Wallet $wallet, Money $amount, TransactionType $transactionType, BalanceType $balanceType): void;

    public function getTotalAvailableBalance(Wallet $wallet, BalanceType $balanceType): Money;
}
