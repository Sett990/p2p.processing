<?php

namespace App\Services\Order;

use App\Contracts\OrderServiceContract;
use App\DTO\Order\CreateOrderDTO;
use App\DTO\Order\SetDetailsToOrderDTO;
use App\Enums\TransactionType;
use App\Exceptions\OrderException;
use App\Models\Order;
use App\Services\Order\Features\OrderDetailSetter;
use App\Services\Order\Features\OrderMaker;
use App\Services\Order\Features\OrderOperator;

class OrderService implements OrderServiceContract
{
    /**
     * @throws OrderException
     */
    public function create(CreateOrderDTO $data): Order
    {
        $order = (new OrderMaker($data))->create();

        if ( !$data->manually) {
            $order = $this->setDetailsToOrder(
                order: $order,
                data: new SetDetailsToOrderDTO(
                    gateway: $data->paymentGateway,
                    subGateway: $data->subPaymentGateway,
                    detailType: $data->paymentDetailType,
                )
            );
        }
        
        return $order;
    }

    /**
     * @throws OrderException
     */
    public function setDetailsToOrder(Order $order, SetDetailsToOrderDTO $data): Order
    {
        return (new OrderDetailSetter($order, $data))->set();
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
