<?php

namespace App\Http\Controllers\Trader;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentDetailResource;
use App\Models\Order;
use App\Models\PaymentDetail;
use App\Services\Money\Currency;
use App\Services\Money\Money;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StatisticController extends Controller
{
    public function index(Request $request)
    {
        // Получаем даты из запроса или устанавливаем значения по умолчанию
        $startDate = $request->input('startDate', now()->subDays(30)->toDateString());
        $endDate = $request->input('endDate', now()->toDateString());
        
        // Получаем платежные реквизиты трейдера с пагинацией
        $paymentDetails = PaymentDetail::with(['paymentGateway'])
            ->where('user_id', auth()->id())
            ->paginate(5);
        
        // Преобразуем реквизиты и добавляем дополнительные данные
        $paymentDetailsCollection = $paymentDetails->getCollection()->map(function ($detail) use ($startDate, $endDate) {
            $detail->turnover = 1000; // Фиксированное значение в $1000
            $detail->orders_count = Order::where('payment_detail_id', $detail->id)
                ->where('status', OrderStatus::SUCCESS)
                ->whereBetween('finished_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                ->count();
            
            return $detail;
        });
        
        // Заменяем коллекцию в пагинаторе
        $paymentDetails->setCollection($paymentDetailsCollection);
        
        // Преобразуем в ресурс
        $paymentDetails = PaymentDetailResource::collection($paymentDetails);
        
        // Получаем закрытые сделки трейдера с пагинацией
        $closedOrders = Order::with(['paymentDetail', 'paymentGateway'])
            ->whereRelation('paymentDetail', 'user_id', auth()->id())
            ->whereNotNull('trader_paid_for_order')
            ->where('status', OrderStatus::SUCCESS)
            ->whereBetween('finished_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->orderBy('finished_at', 'desc')
            ->paginate(10);
        
        // Преобразуем сделки в нужный формат
        $closedOrdersCollection = $closedOrders->getCollection()->map(function ($order) {
            return [
                'id' => $order->id,
                'uuid' => $order->uuid,
                'amount_usdt' => $order->amount->toInt(),
                'trader_paid_for_order' => $order->trader_paid_for_order->toInt(),
                'trader_profit' => $order->trader_profit->toInt(),
                'commission_rate' => $order->trader_commission_rate,
                'payment_gateway_name' => $order->paymentGateway->name,
                'payment_detail_name' => $order->paymentDetail->name,
                'finished_at' => $order->finished_at->toDateTimeString(),
            ];
        });
        
        // Заменяем коллекцию в пагинаторе
        $closedOrders->setCollection($closedOrdersCollection);
        
        return Inertia::render('Statistic/Trader/Index', [
            'paymentDetails' => $paymentDetails,
            'closedOrders' => $closedOrders,
            'filters' => [
                'startDate' => $startDate,
                'endDate' => $endDate
            ]
        ]);
    }
}
