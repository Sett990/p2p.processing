<?php

namespace App\Contracts;

use App\Enums\BalanceType;
use App\Enums\TransactionType;
use App\Models\User;
use App\Models\Wallet;
use App\Services\Money\Money;

interface WalletServiceContract
{
    public function create(User $user): Wallet;

    public function takeFormBalance(Wallet $wallet, Money $amount, TransactionType $transactionType, BalanceType $balanceType): void;

    public function giveToBalance(Wallet $wallet, Money $amount, TransactionType $transactionType, BalanceType $balanceType): void;

    public function takeFromMerchant(Wallet $wallet, Money $amount): void;

    public function giveToMerchant(Wallet $wallet, Money $amount): void;

    public function takeFromTrust(Wallet $wallet, Money $amount, TransactionType $type): void;

    public function giveToTrust(Wallet $wallet, Money $amount, TransactionType $type): void;

    public function getMaxReserveBalance(): int;
}
