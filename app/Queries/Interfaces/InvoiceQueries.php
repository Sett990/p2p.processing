<?php

namespace App\Queries\Interfaces;

use App\Enums\BalanceType;
use App\Models\Wallet;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface InvoiceQueries
{
    public function paginate(Wallet $wallet, BalanceType $balanceType): LengthAwarePaginator;
}
