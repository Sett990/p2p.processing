<?php

namespace App\Contracts;

use App\Enums\BalanceType;
use App\Enums\NetworkEnum;
use App\Exceptions\InvoiceException;
use App\Models\Invoice;
use App\Models\Wallet;
use App\Services\Money\Money;

interface InvoiceServiceContract
{
    /**
     * @throws InvoiceException
     */
    public function createWithdrawal(Wallet $wallet, Money $amount, ?string $address, BalanceType $balanceType): Invoice;

    /**
     * @throws InvoiceException
     */
    public function createAutoWithdrawal(Wallet $wallet, Money $amount, string $address, NetworkEnum $network): Invoice;

    /**
     * @throws InvoiceException
     */
    public function finishWithdrawal($invoice): void;

    /**
     * @throws InvoiceException
     */
    public function cancelWithdrawal($invoice): void;

    /**
     * @throws InvoiceException
     */
    public function deposit(Wallet $wallet, Money $amount, BalanceType $balanceType, string $transactionID = null): void;

    /**
     * @throws InvoiceException
     */
    public function withdraw(Wallet $wallet, Money $amount, BalanceType $balanceType): void;
}
