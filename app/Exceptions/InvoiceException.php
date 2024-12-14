<?php

namespace App\Exceptions;

class InvoiceException extends BaseException
{
    public static function insufficientBalance(): static
    {
        return make('Недостаточно средств на балансе.');
    }

    public static function invalidInvoiceType(): static
    {
        return make('Неверный тип инвойса.');
    }

    public static function invoiceAlreadyFinished(): static
    {
        return make('Инвойс уже завершен.');
    }
}
