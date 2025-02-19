<?php

namespace App\Contracts;

use App\DTO\Order\CreateOrderDTO;
use App\DTO\Order\AssignDetailsToOrderDTO;
use App\Enums\OrderSubStatus;
use App\Exceptions\OrderException;
use App\Models\Order;
use App\Services\Money\Money;

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
    public function finishOrderAsSuccessful(Order $order, OrderSubStatus $subStatus): void;

    /**
     * @throws OrderException
     */
    public function finishOrderAsFailed(Order $order, OrderSubStatus $subStatus): void;

    /**
     * @throws OrderException
     */
    public function reopenFinishedOrder(Order $order, OrderSubStatus $subStatus): void;

    /**
     * @throws OrderException
     */
    public function updateAmount(Order $order, Money $amount): bool;
}
