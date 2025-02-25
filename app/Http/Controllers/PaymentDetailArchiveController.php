<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Models\PaymentDetail;
use Illuminate\Support\Facades\Gate;

class PaymentDetailArchiveController extends Controller
{
    public function store(PaymentDetail $paymentDetail)
    {
        Gate::authorize('access-to-payment-detail', $paymentDetail);

        if ($paymentDetail->orders()->where('status', OrderStatus::PENDING)->exists()) {
            return redirect()->back()->with('error', 'Реквизит не должен иметь открытые сделки.');
        }

        $paymentDetail->update([
            'archived_at' => now(),
            'is_active' => false,
        ]);

        return redirect()->back();
    }
}
