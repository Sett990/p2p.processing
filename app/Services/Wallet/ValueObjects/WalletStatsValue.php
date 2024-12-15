<?php

namespace App\Services\Wallet\ValueObjects;

use App\Services\Money\Money;
use Illuminate\Support\Collection;

/**
 * @property Collection<string, Money> $totalAvailableBalances
 * @property Collection<string, Money> $lockedForWithdrawalBalances
 */
class WalletStatsValue extends ValueObject
{
    public function __construct(
        protected Collection $totalAvailableBalances,
        protected Collection $lockedForWithdrawalBalances,
        protected EscrowBalances $escrowBalances,
    )
    {}

    public function toArray(): array
    {
        $result['totalAvailableBalances'] = $this->totalAvailableBalances->transform(function (Money $item) {
            return $item->toBeauty();
        })->toArray();

        $result['lockedForWithdrawalBalances'] = $this->lockedForWithdrawalBalances->transform(function (Money $item) {
            return $item->toBeauty();
        })->toArray();

        $result['escrowBalances'] = array_map(function (EscrowBalance $item) {
            return [
                'amount' => $item->balance->toBeauty(),
                'count' => $item->count,
            ];
        }, get_object_vars($this->escrowBalances));

        return $result;
    }
}
