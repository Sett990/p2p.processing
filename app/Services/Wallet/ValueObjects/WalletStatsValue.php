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
        protected Collection   $totalAvailableBalances,
        protected Collection   $lockedForWithdrawalBalances,
        protected EscrowsValue $escrowBalances,
        protected CurrencyValue $currency,
    )
    {}

    public function toArray(): array
    {
        $result['totalAvailableBalances'] = $this->totalAvailableBalances->transform(function (BalanceValue $item) {
            return [
                'primaryAmount' => $item->primaryAmount->toBeauty(),
                'secondaryAmount' => $item->secondaryAmount->toBeauty()
            ];
        })->toArray();

        $result['lockedForWithdrawalBalances'] = $this->lockedForWithdrawalBalances->transform(function (BalanceValue $item) {
            return [
                'primaryAmount' => $item->primaryAmount->toBeauty(),
                'secondaryAmount' => $item->secondaryAmount->toBeauty()
            ];
        })->toArray();

        $result['escrowBalances'] = array_map(function (EscrowValue $item) {
            return [
                'balance' => [
                    'primaryAmount' => $item->balance->primaryAmount->toBeauty(),
                    'secondaryAmount' => $item->balance->secondaryAmount->toBeauty()
                ],
                'count' => $item->count,
            ];
        }, get_object_vars($this->escrowBalances));

        $result['currency'] = [
            'primaryCurrency' => $this->currency->primaryCurrency->getCode(),
            'secondaryCurrency' => $this->currency->secondaryCurrency->getCode(),
        ];

        return $result;
    }
}
