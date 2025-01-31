<?php

namespace App\Contracts;

use App\DTO\Order\CreateOrderDTO;
use App\DTO\Order\AssignDetailsToOrderDTO;
use App\Exceptions\OrderException;
use App\Models\Order;

interface OrderServiceContract
{
    /**
     * @throws OrderException
     */
    public function create(CreateOrderDTO $data): Order;

    /**
     * @throws OrderException
     */
    public function assignDetailsToOrder(Order $order, AssignDetailsToOrderDTO $data): Order;

    /**
     * @throws OrderException
     */
    public function finishOrderAsSuccessful(Order $order): void;

    /**
     * @throws OrderException
     */
    public function finishOrderAsFailed(Order $order): void;

    /**
     * @throws OrderException
     */
    public function reopenFinishedOrder(Order $order): void;
}
