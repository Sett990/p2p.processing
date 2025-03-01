<?php

namespace App\Exports;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;

class DealsExport implements FromCollection, WithHeadings, WithMapping, WithCustomStartCell
{
    public function __construct(
        protected User $user,
    )
    {}

    public function startCell(): string
    {
        return 'A1';
    }

    public function collection(): Collection
    {
        return Order::query()
            ->with('paymentDetail', 'paymentGateway')
            ->whereRelation('paymentDetail', 'user_id', $this->user->id)
            ->where('status', OrderStatus::SUCCESS)
            ->get();
    }

    public function headings(): array
    {
        return [
            [
                'UUID',
                'Сумма',
                'Сумма USDT',
                'Доход',
                'Валюта',
                'Курс конвертации',
                'Наценка на курс %',
                'Статус',
                'Платежный метод (код)',
                'Платежный метод (имя)',
                'Реквизит',
                'Имя реквизита',
                'Закрыт',
                'Создан'
            ],
            [
                'uuid',
                'amount',
                'amount_usdt',
                'profit',
                'currency',
                'conversion_price',
                'markup_rate',
                'status',
                'payment_gateway_code',
                'payment_gateway_name',
                'payment_detail',
                'payment_detail_name',
                'finished_at',
                'created_at'
            ]
        ];
    }

    public function map($row): array
    {
        $order = $row;
        /**
         * @var Order $order
         */
        return [
            $order->uuid,
            $order->amount->toBeauty(),
            $order->profit->toBeauty(),
            $order->trader_profit->toBeauty(),
            $order->currency->getCode(),
            $order->conversion_price->toBeauty(),
            $order->trader_commission_rate,
            $order->status->value,
            $order->paymentGateway->code,
            $order->paymentGateway->name,
            $order->paymentDetail->detail,
            $order->paymentDetail->name,
            $order->finished_at->toDateTimeString(),
            $order->created_at->toDateTimeString(),
        ];
    }
}
