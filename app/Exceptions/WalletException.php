<?php

namespace App\Exceptions;

class WalletException extends BaseException
{
    public static function invalidTransactionTypeForTake()
    {
        return make('Invalid transaction type for take from wallet.');
    }

    public static function invalidTransactionTypeForGive()
    {
        return make('Invalid transaction type for give to wallet.');
    }
}
