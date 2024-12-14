<?php

namespace App\Exceptions;

class InvoiceException extends BaseException
{
    public static function insufficientBalance(): static
    {
        return make('Недостаточно средств на балансе.');
    }
}
