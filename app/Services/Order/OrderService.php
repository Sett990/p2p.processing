<?php

namespace App\Services\Order;

use App\Contracts\OrderServiceContract;
use App\DTO\Order\OrderCreateDTO;
use App\Enums\OrderStatus;
use App\Enums\TransactionType;
use App\Exceptions\OrderException;
use App\Models\Order;
use App\Models\PaymentGateway;
use App\Services\Money\Currency;
use App\Services\Money\Money;
use App\Services\Order\Features\CreateOrder;
use App\Services\Order\Features\FailOrder;
use App\Services\Order\Features\RollbackOrder;
use App\Services\Order\Features\SetPaymentDetailFeature;
use App\Services\Order\Features\SucceedOrder;
use App\DTO\Order\CreateOrderDTO;
use App\DTO\Order\AssignDetailsToOrderDTO;
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

    public function assignDetailsToOrder(Order $order, AssignDetailsToOrderDTO $data): Order
    {
        return $this->lock(function () use ($order, $data) {
            return (new OrderDetailAssigner($order, $data))->assign();
        }, key: $order->id);
    }

    public function finishOrderAsSuccessful(Order $order): void
    {
        $this->lock(function () use ($order) {
            (new OrderOperator($order))->finishOrderAsSuccessful();
        }, key: $order->id);
    }

    public function finishOrderAsFailed(Order $order): void
    {
        $this->lock(function () use ($order) {
            (new OrderOperator($order))->finishOrderAsFailed();
        }, key: $order->id);
    }

    public function reopenFinishedOrder(Order $order): void
    {
        $this->lock(function () use ($order) {
            (new OrderOperator($order))->reopenFinishedOrder();
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

    public function updateAmount(Order $order, Money $amount): bool
    {
        if ($order->status->notEquals(OrderStatus::FAIL)) {
            throw OrderException::make('Order must be failed.');
        }

        $profit = $amount
            ->convert($order->conversion_price, Currency::USDT());
        $serviceProfit = $profit->mul($order->service_commission_rate_total / 100);
        $merchantProfit = $profit->sub($serviceProfit);

        $traderMarkup = $amount
            ->convert($order->base_conversion_price, Currency::USDT())
            ->sub($profit);

        $amountUpdatesHistory = $order->amount_updates_history;

        $amountUpdatesHistory[] = [
            'old_amount' => $order->amount->toBeauty(),
            'new_amount' => $amount->toBeauty(),
            'by_user_id' => auth()->id(),
            'updated_at' => now()->toDateTimeString(),
        ];

        return $order->update([
            'amount' => $amount,
            'trader_profit' => $traderMarkup,
            'profit' => $profit,
            'merchant_profit' => $merchantProfit,
            'service_profit' => $serviceProfit,
            'amount_updates_history' => $amountUpdatesHistory
        ]);
    }
}
