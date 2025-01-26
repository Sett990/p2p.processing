<?php

namespace App\Exceptions;

class InvoiceException extends BaseException
{
    public static function insufficientBalance(): static
    {
        return new self('Недостаточно средств на балансе.');
    }

    public static function invalidInvoiceType(): static
    {
        return new self('Неверный тип инвойса.');
    }

    public static function invoiceAlreadyFinished(): static
    {
        return new self('Инвойс уже завершен.');
    }
}
