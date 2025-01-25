<?php

namespace App\Services\Order;

use App\Contracts\OrderServiceContract;
use App\DTO\Order\OrderCreateDTO;
use App\Enums\TransactionType;
use App\Exceptions\OrderException;
use App\Models\Order;
use App\Models\PaymentGateway;
use App\Services\Order\Features\OrderDetailSetter;
use App\Services\Order\Features\OrderMaker;
use App\Services\Order\Features\OrderOperator;

class OrderService implements OrderServiceContract
{
    /**
     * @throws OrderException
     */
    public function create(OrderCreateDTO $dto): Order
    {
        return (new OrderMaker($dto))->create();
    }

    /**
     * @throws OrderException
     */
    public function setDetailsToOrder(Order $order, PaymentGateway $paymentGateway): Order
    {
        return (new OrderDetailSetter($order, $paymentGateway))->set();
    }

    /**
     * @throws OrderException
     */
    public function finishOrderAsSuccessful(Order $order): bool
    {
        return (new OrderOperator($order))->finishOrderAsSuccessful();
    }

    /**
     * @throws OrderException
     */
    public function finishOrderAsFailed(Order $order, TransactionType $transactionType): bool
    {
        return (new OrderOperator($order))->finishOrderAsFailed($transactionType);
    }

    /**
     * @throws OrderException
     */
    public function reopenFinishedOrder(Order $order, TransactionType $transactionType): bool
    {
        return (new OrderOperator($order))->reopenFinishedOrder($transactionType);
    }
}
