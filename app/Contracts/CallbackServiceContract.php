<?php

namespace App\Contracts;

use App\Models\Order;

interface CallbackServiceContract
{
    public function sendForOrder(Order $order): void;

}
