<?php

namespace App\Exceptions;

class PayoutException extends BaseException
{
    public static function offerAlreadyExists(): PayoutException
    {
        return new self('У вас уже есть предложение для выбранного метода.');
    }
}
