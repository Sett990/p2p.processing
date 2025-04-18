<?php

namespace App\Http\Controllers\MerchantSupport;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use Inertia\Inertia;

class PaymentController extends Controller
{
    public function index()
    {
        $filters = $this->getTableFilters();
        $filtersVariants = $this->getFiltersData();

        $orders = queries()->order()->paginateForMerchant(auth()->user(), $filters);
        $orders = OrderResource::collection($orders);

        return Inertia::render('MerchantSupport/Payment/Index', compact('orders', 'filters', 'filtersVariants'));
    }
} 