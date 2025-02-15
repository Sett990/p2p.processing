<?php

namespace App\Services\Invoice;

use App\Contracts\InvoiceServiceContract;
use App\Enums\BalanceType;
use App\Enums\InvoiceStatus;
use App\Enums\InvoiceType;
use App\Enums\TransactionType;
use App\Exceptions\InvoiceException;
use App\Models\Invoice;
use App\Models\Wallet;
use App\Services\Money\Currency;
use App\Services\Money\Money;

class InvoiceService implements InvoiceServiceContract
{
    public function createWithdrawal(Wallet $wallet, Money $amount, ?string $address, BalanceType $balanceType): Invoice
    {
        $totalAvailableBalance = services()->wallet()->getTotalAvailableBalance($wallet, $balanceType);

        if ($amount->greaterThan($totalAvailableBalance)) {
            throw InvoiceException::insufficientBalance();
        }

        $invoice = Invoice::create([
            'amount' => $amount,
            'currency' => $amount->getCurrency(),
            'address' => $address,
            'type' => InvoiceType::WITHDRAWAL,
            'balance_type' => $balanceType,
            'status' => InvoiceStatus::PENDING,
            'wallet_id' => $wallet->id,
        ]);

        services()->wallet()
            ->takeFormBalance(
                wallet: $wallet,
                amount: $amount,
                transactionType: TransactionType::WITHDRAWAL_BY_USER,
                balanceType: $balanceType
            );

        return $invoice;
    }

    public function finishWithdrawal($invoice): void
    {
        if ($invoice->type->notEquals(InvoiceType::WITHDRAWAL)) {
            throw InvoiceException::invalidInvoiceType();
        }

        if ($invoice->status->notEquals(InvoiceStatus::PENDING)) {
            throw InvoiceException::invoiceAlreadyFinished();
        }

        $invoice->update(['status' => InvoiceStatus::SUCCESS]);
    }

    public function cancelWithdrawal($invoice): void
    {
        if ($invoice->type->notEquals(InvoiceType::WITHDRAWAL)) {
            throw InvoiceException::invalidInvoiceType();
        }

        if ($invoice->status->notEquals(InvoiceStatus::PENDING)) {
            throw InvoiceException::invoiceAlreadyFinished();
        }

        $invoice->update(['status' => InvoiceStatus::FAIL]);

        services()->wallet()->giveToBalance(
            wallet: $invoice->wallet,
            amount: $invoice->amount,
            transactionType: TransactionType::ROLLBACK_FOR_USER_WITHDRAWAL,
            balanceType: $invoice->balance_type
        );
    }

    public function deposit(Wallet $wallet, Money $amount, BalanceType $balanceType): void
    {
        Invoice::create([
            'amount' => $amount,
            'currency' => Currency::USDT(),
            'address' => null,
            'type' => InvoiceType::DEPOSIT,
            'balance_type' => $balanceType,
            'status' => InvoiceStatus::SUCCESS,
            'wallet_id' => $wallet->id,
        ]);

        services()->wallet()
            ->giveToBalance(
                wallet: $wallet,
                amount: $amount,
                transactionType: TransactionType::DEPOSIT_BY_ADMIN,
                balanceType: $balanceType
            );
    }

    public function withdraw(Wallet $wallet, Money $amount, BalanceType $balanceType): void
    {
        $totalAvailableBalance = services()->wallet()->getTotalAvailableBalance($wallet, $balanceType);

        if ($amount->greaterThan($totalAvailableBalance)) {
            throw InvoiceException::insufficientBalance();
        }

        Invoice::create([
            'amount' => $amount,
            'currency' => Currency::USDT(),
            'address' => null,
            'type' => InvoiceType::WITHDRAWAL,
            'balance_type' => $balanceType,
            'status' => InvoiceStatus::SUCCESS,
            'wallet_id' => $wallet->id,
        ]);

        services()->wallet()
            ->takeFormBalance(
                wallet: $wallet,
                amount: $amount,
                transactionType: TransactionType::WITHDRAWAL_BY_ADMIN,
                balanceType: $balanceType
            );
    }
}
