<?php

namespace App\Exceptions;

class OrderException extends BaseException
{
    public static function orderAlreadyFinished($order)
    {
        return new self("Сделка уже завершена. Order ID: $order->id");
    }

    public static function orderAlreadyOpened($order)
    {
        return new self("Сделка уже открыта. Order ID: $order->id");
    }
}
