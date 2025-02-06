<?php

namespace App\Contracts;

use App\DTO\Order\OrderCreateDTO;
use App\Enums\TransactionType;
use App\Exceptions\OrderException;
use App\Models\Order;
use App\Models\PaymentGateway;
use App\Services\Money\Money;

interface OrderServiceContract
{
    /**
     * @throws OrderException
     */
    public function create(OrderCreateDTO $dto): Order;

    /**
     * @throws OrderException
     */
    public function setPaymentDetail(Order $order, PaymentGateway $paymentGateway): Order;

    /**
     * @throws OrderException
     */
    public function succeed(Order $order): bool;

    /**
     * @throws OrderException
     */
    public function fail(Order $order, TransactionType $transactionType): bool;

    /**
     * @throws OrderException
     */
    public function rollback(Order $order, TransactionType $transactionType): bool;

    public function updateAmount(Order $order, Money $amount): bool;
}
