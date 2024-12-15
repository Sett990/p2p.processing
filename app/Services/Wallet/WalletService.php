<?php

namespace App\Services\Wallet;

use App\Contracts\WalletServiceContract;
use App\Enums\BalanceType;
use App\Enums\InvoiceStatus;
use App\Enums\InvoiceType;
use App\Enums\OrderStatus;
use App\Enums\TransactionType;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\User;
use App\Models\Wallet;
use App\Services\Money\Currency;
use App\Services\Money\Money;
use App\Services\Wallet\GiveToBalanceHandler\GiveToMerchant;
use App\Services\Wallet\GiveToBalanceHandler\GiveToTrust;
use App\Services\Wallet\TakeFromBalanceHandler\TakeFromMerchant;
use App\Services\Wallet\TakeFromBalanceHandler\TakeFromTrust;
use App\Services\Wallet\ValueObjects\EscrowBalance;
use App\Services\Wallet\ValueObjects\EscrowBalances;
use App\Services\Wallet\ValueObjects\WalletStatsValue;

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

    public function getWalletStats(Wallet $wallet): WalletStatsValue
    {
        $totalAvailableBalances = collect();

        foreach (BalanceType::cases() as $balanceType) {
            $totalAvailableBalances->put($balanceType->value, $this->getTotalAvailableBalance($wallet, $balanceType));
        }

        //===

        $lockedForWithdrawalBalances = collect();

        foreach (BalanceType::cases() as $balanceType) {
            $value = Invoice::query()
                ->where('type', InvoiceType::WITHDRAWAL)
                ->where('wallet_id', $wallet->id)
                ->where('status', InvoiceStatus::PENDING)
                ->where('balance_type', BalanceType::TRUST)
                ->sum('amount');

            $lockedForWithdrawalBalances->put($balanceType->value, Money::fromUnits($value, Currency::USDT()));
        }

        //===

        $escrowOrdersQuery = Order::query()
            ->where('status', OrderStatus::PENDING)
            ->whereRelation('paymentDetail', 'user_id', $wallet->user_id)
            ->whereDoesntHave('dispute');

        $escrowOrdersBalance = Money::fromUnits($escrowOrdersQuery->sum('profit'), Currency::USDT());
        $escrowOrdersCount = $escrowOrdersQuery->count();

        //===

        $disputeOrdersQuery = Order::query()
            ->where('status', OrderStatus::PENDING)
            ->whereRelation('paymentDetail', 'user_id', $wallet->user_id)
            ->whereHas('dispute');

        $escrowDisputeBalance = Money::fromUnits($disputeOrdersQuery->sum('profit'), Currency::USDT());
        $escrowDisputeCount = $disputeOrdersQuery->count();

        return new WalletStatsValue(
            totalAvailableBalances: $totalAvailableBalances,
            lockedForWithdrawalBalances: $lockedForWithdrawalBalances,
            escrowBalances: new EscrowBalances(
                orders: new EscrowBalance($escrowOrdersBalance, $escrowOrdersCount),
                disputes: new EscrowBalance($escrowDisputeBalance, $escrowDisputeCount)
            )
        );
    }
}
