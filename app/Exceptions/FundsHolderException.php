<?php

namespace App\Exceptions;

class FundsHolderException extends BaseException
{
    public static function invalidAmountCurrency(): FundsHolderException
    {
        return new self('Неподдерживаемая валюта.');
    }
}
