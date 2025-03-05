<?php

namespace App\Services\Invoice;

use App\Contracts\InvoiceServiceContract;
use App\Enums\BalanceType;
use App\Enums\InvoiceStatus;
use App\Enums\InvoiceType;
use App\Enums\NetworkEnum;
use App\Enums\TransactionType;
use App\Exceptions\InvoiceException;
use App\Models\Invoice;
use App\Models\Wallet;
use App\Services\Money\Currency;
use App\Services\Money\Money;
use App\Utils\Transaction;
use Illuminate\Support\Facades\Http;

class InvoiceService implements InvoiceServiceContract
{
    public function createWithdrawal(Wallet $wallet, Money $amount, ?string $address, BalanceType $balanceType): Invoice
    {
        return $this->lock(function () use ($wallet, $amount, $address, $balanceType) {
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
                ->takeFromBalance(
                    walletID: $wallet->id,
                    amount: $amount,
                    transactionType: TransactionType::WITHDRAWAL_BY_USER,
                    balanceType: $balanceType
                );

            return $invoice;
        }, $wallet);
    }

    public function createAutoWithdrawal(Wallet $wallet, Money $amount, string $address, NetworkEnum $network): Invoice
    {
        return $this->lock(function () use ($wallet, $amount, $network, $address) {
            $totalAvailableBalance = services()->wallet()->getTotalAvailableBalance($wallet, BalanceType::MERCHANT);

            if ($amount->greaterThan($totalAvailableBalance)) {
                throw InvoiceException::insufficientBalance();
            }

            $invoice = Transaction::run(function () use ($wallet, $amount, $network, $address) {
                $invoice = Invoice::create([
                    'amount' => $amount,
                    'currency' => $amount->getCurrency(),
                    'address' => $address,
                    'network' => $network,
                    'type' => InvoiceType::WITHDRAWAL,
                    'balance_type' => BalanceType::MERCHANT,
                    'status' => InvoiceStatus::SUCCESS,
                    'wallet_id' => $wallet->id,
                ]);

                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'X-API-Key' => config('api.withdrawal_service_x_api_key'),
                ])->post(config('api.withdrawal_service_host'), [
                    'payment_id' => $invoice->id,
                    'email' => $wallet->user->email,
                    'user_id' => $wallet->user->id,
                    'network' => $network->value,
                    'address' => $address,
                    'amount' => $amount->toBeauty(),
                ]);

                if (!$response->successful() || !isset($response->json()['status']) || $response->json()['status'] !== 'success') {
                    throw InvoiceException::unableToWithdrawByService();
                }

                $data = $response->json();

                $invoice->update([
                    'external_id' => $data['transaction_id'],
                    'tx_hash' => $data['tx_hash'],
                ]);

                services()->wallet()
                    ->takeFromBalance(
                        walletID: $wallet->id,
                        amount: $amount,
                        transactionType: TransactionType::WITHDRAWAL_BY_USER,
                        balanceType: BalanceType::MERCHANT
                    );

                return $invoice;
            });

            return $invoice;
        }, $wallet);
    }

    public function finishWithdrawal(Invoice $invoice): void
    {
        $this->lock(function () use ($invoice) {
            if ($invoice->type->notEquals(InvoiceType::WITHDRAWAL)) {
                throw InvoiceException::invalidInvoiceType();
            }

            if ($invoice->status->notEquals(InvoiceStatus::PENDING)) {
                throw InvoiceException::invoiceAlreadyFinished();
            }

            $invoice->update(['status' => InvoiceStatus::SUCCESS]);
        }, $invoice->wallet);
    }

    public function cancelWithdrawal(Invoice $invoice): void
    {
        $this->lock(function () use ($invoice) {
            if ($invoice->type->notEquals(InvoiceType::WITHDRAWAL)) {
                throw InvoiceException::invalidInvoiceType();
            }

            if ($invoice->status->notEquals(InvoiceStatus::PENDING)) {
                throw InvoiceException::invoiceAlreadyFinished();
            }

            $invoice->update(['status' => InvoiceStatus::FAIL]);

            services()->wallet()->giveToBalance(
                walletID: $invoice->wallet->id,
                amount: $invoice->amount,
                transactionType: TransactionType::ROLLBACK_FOR_USER_WITHDRAWAL,
                balanceType: $invoice->balance_type
            );
        }, $invoice->wallet);
    }

    public function deposit(Wallet $wallet, Money $amount, BalanceType $balanceType, string $transactionID = null): void
    {
        $this->lock(function () use ($transactionID, $wallet, $amount, $balanceType) {
            if ($transactionID && Invoice::where('transaction_id', $transactionID)->exists()) {
                throw InvoiceException::invoiceAlreadyExists();
            }

            Invoice::create([
                'amount' => $amount,
                'currency' => Currency::USDT(),
                'address' => null,
                'type' => InvoiceType::DEPOSIT,
                'balance_type' => $balanceType,
                'status' => InvoiceStatus::SUCCESS,
                'transaction_id' => $transactionID,
                'wallet_id' => $wallet->id,
            ]);

            services()->wallet()
                ->giveToBalance(
                    walletID: $wallet->id,
                    amount: $amount,
                    transactionType: $transactionID ? TransactionType::DEPOSIT_BY_USER : TransactionType::DEPOSIT_BY_ADMIN,
                    balanceType: $balanceType
                );
        }, $wallet);
    }

    public function withdraw(Wallet $wallet, Money $amount, BalanceType $balanceType): void
    {
        $this->lock(function () use ($wallet, $amount, $balanceType) {
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
                ->takeFromBalance(
                    walletID: $wallet->id,
                    amount: $amount,
                    transactionType: TransactionType::WITHDRAWAL_BY_ADMIN,
                    balanceType: $balanceType
                );
        }, $wallet);
    }

    protected function lock(callable $callback, Wallet $wallet): mixed
    {
        return cache()->lock('invoice-lock-'.$wallet->id, 8)
            ->block(10, function () use ($callback) {
                return Transaction::run(function() use ($callback) {
                    return $callback();
                });
            });
    }
}
