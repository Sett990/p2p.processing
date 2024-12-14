<?php

namespace App\Contracts;

use App\Enums\BalanceType;
use App\Exceptions\InvoiceException;
use App\Models\Invoice;
use App\Models\Wallet;
use App\Services\Money\Money;

interface InvoiceServiceContract
{
    /**
     * @throws InvoiceException
     */
    public function createWithdrawal(Wallet $wallet, Money $amount, string $address, BalanceType $balanceType): Invoice;

    /**
     * @throws InvoiceException
     */
    public function deposit(Wallet $wallet, Money $amount, BalanceType $balanceType): void;

    /**
     * @throws InvoiceException
     */
    public function withdraw(Wallet $wallet, Money $amount, BalanceType $balanceType): void;
}
