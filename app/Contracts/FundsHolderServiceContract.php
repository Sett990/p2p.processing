<?php

namespace App\Contracts;

use App\Enums\BalanceType;
use App\Models\FundsOnHold;
use App\Models\Payout;
use App\Models\Wallet;
use App\Services\Money\Money;
use Carbon\Carbon;

interface FundsHolderServiceContract
{
    public function holdFundsFor(Money $amount, Wallet $sourceWallet, Wallet $destinationWallet, BalanceType $sourceWalletBalanceType, BalanceType $destinationWalletBalanceType, Payout $forAction, Carbon $until): FundsOnHold;
}
