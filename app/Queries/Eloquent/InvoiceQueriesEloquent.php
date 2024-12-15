<?php

namespace App\Queries\Eloquent;

use App\Enums\BalanceType;
use App\Models\Invoice;
use App\Models\Wallet;
use App\Queries\Interfaces\InvoiceQueries;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class InvoiceQueriesEloquent implements InvoiceQueries
{
    public function paginate(Wallet $wallet, BalanceType $balanceType): LengthAwarePaginator
    {
        return Invoice::query()
            ->with('wallet.user')
            ->where('wallet_id', $wallet->id)
            ->where('balance_type', $balanceType)
            ->orderByDesc('id')
            ->paginate(10);
    }
}
