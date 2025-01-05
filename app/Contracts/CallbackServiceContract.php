<?php

namespace App\Contracts;

use App\Models\Order;

interface OrderCallbackServiceContract
{
    public function send(Order $order): void;
}
