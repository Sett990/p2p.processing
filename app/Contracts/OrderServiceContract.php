<?php

namespace App\Contracts;

use App\DTO\Order\CreateOrderDTO;
use App\DTO\Order\SetDetailsToOrderDTO;
use App\Enums\TransactionType;
use App\Exceptions\OrderException;
use App\Models\Order;
use App\Models\PaymentGateway;

interface OrderServiceContract
{
    /**
     * @throws OrderException
     */
    public function create(CreateOrderDTO $data): Order;

    /**
     * @throws OrderException
     */
    public function setDetailsToOrder(Order $order, SetDetailsToOrderDTO $data): Order;

    /**
     * @throws OrderException
     */
    public function finishOrderAsSuccessful(Order $order): bool;

    /**
     * @throws OrderException
     */
    public function finishOrderAsFailed(Order $order, TransactionType $transactionType): bool;

    /**
     * @throws OrderException
     */
    public function reopenFinishedOrder(Order $order, TransactionType $transactionType): bool;
}
