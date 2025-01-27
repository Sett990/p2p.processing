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
        return $this->lock(function () use ($data) {
            $order = (new OrderMaker($data))->create();

            if ( !$data->manually) {
                $order = (new OrderDetailAssigner(
                    order: $order,
                    data: new AssignDetailsToOrderDTO(
                        gateway: $data->paymentGateway,
                        subGateway: $data->subPaymentGateway,
                        detailType: $data->paymentDetailType,
                    )
                ))->assign();
            }

            return $order;
        });
    }

    public function assignDetailsToOrder(Order $order, AssignDetailsToOrderDTO $data): Order
    {
        return $this->lock(function () use ($order, $data) {
            return (new OrderDetailAssigner($order, $data))->assign();
        }, key: $order->id);
    }

    public function finishOrderAsSuccessful(Order $order): bool
    {
        return $this->lock(function () use ($order) {
            return (new OrderOperator($order))->finishOrderAsSuccessful();
        }, key: $order->id);
    }

    public function finishOrderAsFailed(Order $order, TransactionType $transactionType): bool
    {
        return $this->lock(function () use ($order, $transactionType) {
            return (new OrderOperator($order))->finishOrderAsFailed($transactionType);
        }, key: $order->id);
    }

    public function reopenFinishedOrder(Order $order, TransactionType $transactionType): bool
    {
        return $this->lock(function () use ($order, $transactionType) {
            return (new OrderOperator($order))->reopenFinishedOrder($transactionType);
        }, key: $order->id);
    }

    protected function lock(callable $callback, string $key = ''): mixed
    {
        return cache()->lock('order-lock'.$key, 5)
            ->block(8, function () use ($callback) {
                return DB::transaction(function () use ($callback) {
                    return $callback();
                });
            });
    }
}
