<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use Inertia\Inertia;

class OrderController extends Controller
{
    public function index()
    {
        $filters = $this->getTableFilters();
        $filtersVariants = $this->getFiltersData();

        $orders = queries()->order()->paginateForAdmin($filters);
        $orders = OrderResource::collection($orders);

        return Inertia::render('Order/Index', compact('orders', 'filters', 'filtersVariants'));
    }
}
