<?php

namespace App\Services\Wallet;

use App\Contracts\WalletServiceContract;
use App\Enums\BalanceType;
use App\Enums\TransactionType;
use App\Models\User;
use App\Models\Wallet;
use App\Services\Money\Money;
use App\Services\Wallet\ValueObjects\WalletStatsValue;
use Illuminate\Support\Facades\Cache;

class WalletServiceCache implements WalletServiceContract
{
    private WalletServiceContract $walletService;

    public function __construct(WalletServiceContract $walletService)
    {
        $this->walletService = $walletService;
    }

    public function getMaxReserveBalance(): int
    {
        return $this->walletService->getMaxReserveBalance();
    }

    public function create(User $user): Wallet
    {
        return $this->walletService->create($user);
    }

    public function takeFormBalance(Wallet $wallet, Money $amount, TransactionType $transactionType, BalanceType $balanceType): void
    {
        $this->walletService->takeFormBalance($wallet, $amount, $transactionType, $balanceType);
    }

    public function giveToBalance(Wallet $wallet, Money $amount, TransactionType $transactionType, BalanceType $balanceType): void
    {
        $this->walletService->giveToBalance($wallet, $amount, $transactionType, $balanceType);
    }

    public function getTotalAvailableBalance(Wallet $wallet, BalanceType $balanceType): Money
    {
        return $this->walletService->getTotalAvailableBalance($wallet, $balanceType);
    }

    public function getWalletStats(Wallet $wallet): WalletStatsValue
    {
        return Cache::remember("wallet-stats-$wallet->id", 10, function () use ($wallet) {
            return $this->walletService->getWalletStats($wallet);
        });
    }
}
