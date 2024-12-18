<?php

namespace App\Observers;

use App\Enums\BalanceType;
use App\Enums\TransactionDirection;
use App\Jobs\SendTelegramNotificationJob;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Services\TelegramBot\Notifications\LowBalance;

class TransactionObserver
{
    public $afterCommit = true;

    /**
     * Handle the Transaction "created" event.
     */
    public function created(Transaction $transaction): void
    {
        if (
            $transaction->direction->equals(TransactionDirection::OUT)
            && $transaction->balance_type->equals(BalanceType::TRUST)
        )

        if (Wallet::RESERVE_BALANCE / 10 > intval($transaction->wallet->trust_balance->toBeauty()) && $transaction->wallet->user->telegram) {
            SendTelegramNotificationJob::dispatch(
                new LowBalance(
                    telegram: $transaction->wallet->user->telegram,
                    wallet: $transaction->wallet
                )
            );
        }
    }

    /**
     * Handle the Transaction "updated" event.
     */
    public function updated(Transaction $transaction): void
    {
        //
    }

    /**
     * Handle the Transaction "deleted" event.
     */
    public function deleted(Transaction $transaction): void
    {
        //
    }

    /**
     * Handle the Transaction "restored" event.
     */
    public function restored(Transaction $transaction): void
    {
        //
    }

    /**
     * Handle the Transaction "force deleted" event.
     */
    public function forceDeleted(Transaction $transaction): void
    {
        //
    }
}
