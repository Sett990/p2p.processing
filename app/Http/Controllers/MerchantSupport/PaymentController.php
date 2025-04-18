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

        if (auth()->user()->hasRole('Super Admin')) {
            $user = auth()->user();
        } else {
            $user = auth()->user()->merchant;
        }
        $orders = queries()->order()->paginateForMerchant($user, $filters);
        $orders = OrderResource::collection($orders);

        return Inertia::render('MerchantSupport/Payment/Index', compact('orders', 'filters', 'filtersVariants'));
    }
}
