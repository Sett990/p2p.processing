<?php

namespace App\Services\Order;

use App\Contracts\OrderServiceContract;
use App\Enums\OrderStatus;
use App\Enums\OrderSubStatus;
use App\Exceptions\OrderException;
use App\Models\Order;
use App\Services\Money\Money;
use App\DTO\Order\CreateOrderDTO;
use App\DTO\Order\AssignDetailsToOrderDTO;
use App\Services\Order\BusinesLogic\Profits;
use App\Services\Order\Features\OrderDetailAssigner;
use App\Services\Order\Features\OrderMaker;
use App\Services\Order\Features\OrderOperator;
use App\Utils\Transaction;

class OrderService implements OrderServiceContract
{
    public function create(CreateOrderDTO $data): Order
    {
        return $this->lock(function () use ($data) {
            $order = (new OrderMaker($data))->create();

            if (! $data->manually) {
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

    public function assignDetailsToOrder(int $orderID, AssignDetailsToOrderDTO $data): Order
    {
        return $this->lock(function () use ($orderID, $data) {
            $order = Order::where('id', $orderID)->lockForUpdate()->first();

            return (new OrderDetailAssigner($order, $data))->assign();
        }, key: $orderID);
    }

    public function finishOrderAsSuccessful(int $orderID, OrderSubStatus $subStatus): void
    {
        $this->lock(function () use ($orderID, $subStatus) {
            (new OrderOperator($orderID))->finishOrderAsSuccessful($subStatus);
        }, key: $orderID);
    }

    public function finishOrderAsFailed(int $orderID, OrderSubStatus $subStatus): void
    {
        $this->lock(function () use ($orderID, $subStatus) {
            (new OrderOperator($orderID))->finishOrderAsFailed($subStatus);
        }, key: $orderID);
    }

    public function reopenFinishedOrder(int $orderID, OrderSubStatus $subStatus): void
    {
        $this->lock(function () use ($orderID, $subStatus) {
            (new OrderOperator($orderID))->reopenFinishedOrder($subStatus);
        }, key: $orderID);
    }

    public function updateAmount(int $orderID, Money $amount): bool
    {
        //TODO перенести код в отдельный обработчик
        //TODO нужно не только пересчитывать суммы, но и списывать и зачислять этин новые суммы трейдеру, мерчанту и сервису
        return $this->lock(function () use ($orderID, $amount) {
            $order = Order::where('id', $orderID)->lockForUpdate()->first();

            if ($order->status->notEquals(OrderStatus::FAIL) && !($order->dispute && $order->status->equals(OrderStatus::PENDING))) {
                throw OrderException::make('Order must be failed or has opened dispute.');
            }

            $profits = Profits::calculate(
                amount: $amount,
                exchangeRate: $order->conversion_price,
                totalCommissionRate: $order->total_service_commission_rate,
                traderCommissionRate: $order->trader_commission_rate,
            );

            $amountUpdatesHistory = $order->amount_updates_history;

            $amountUpdatesHistory[] = [
                'old_amount' => $order->amount->toBeauty(),
                'new_amount' => $amount->toBeauty(),
                'by_user_id' => auth()->id(),
                'updated_at' => now()->toDateTimeString(),
            ];

            return $order->update([
                'amount' => $amount,
                'trader_profit' => $profits->traderProfit,
                'total_profit' => $profits->totalProfit,
                'merchant_profit' => $profits->merchantProfit,
                'service_profit' => $profits->serviceProfit,
                'amount_updates_history' => $amountUpdatesHistory
            ]);
        }, key: $orderID);
    }

    protected function lock(callable $callback, string $key = ''): mixed
    {
        return cache()->lock('order-lock'.$key, 8)
            ->block(10, function () use ($callback) {
                return Transaction::run(function () use ($callback) {
                    return $callback();
                });
            });
    }
}
