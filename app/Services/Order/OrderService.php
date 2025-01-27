<?php

namespace App\Services\Order;

use App\Contracts\OrderServiceContract;
use App\DTO\Order\CreateOrderDTO;
use App\DTO\Order\AssignDetailsToOrderDTO;
use App\Enums\TransactionType;
use App\Models\Order;
use App\Services\Order\Features\OrderDetailAssigner;
use App\Services\Order\Features\OrderMaker;
use App\Services\Order\Features\OrderOperator;
use Illuminate\Support\Facades\DB;

class OrderService implements OrderServiceContract
{
    public function create(CreateOrderDTO $data): Order
    {
        return DB::transaction(function () use ($data) {
            $order = (new OrderMaker($data))->create();

            if ( !$data->manually) {
                $order = $this->assignDetailsToOrder(
                    order: $order,
                    data: new AssignDetailsToOrderDTO(
                        gateway: $data->paymentGateway,
                        subGateway: $data->subPaymentGateway,
                        detailType: $data->paymentDetailType,
                    )
                );
            }

            return $order;
        });
    }

    public function assignDetailsToOrder(Order $order, AssignDetailsToOrderDTO $data): Order
    {
        return (new OrderDetailAssigner($order, $data))->assign();
    }

    public function finishOrderAsSuccessful(Order $order): bool
    {
        return (new OrderOperator($order))->finishOrderAsSuccessful();
    }

    public function finishOrderAsFailed(Order $order, TransactionType $transactionType): bool
    {
        return (new OrderOperator($order))->finishOrderAsFailed($transactionType);
    }

    public function reopenFinishedOrder(Order $order, TransactionType $transactionType): bool
    {
        return (new OrderOperator($order))->reopenFinishedOrder($transactionType);
    }
}
