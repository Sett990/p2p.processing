<?php

namespace App\Http\Controllers\Admin;

use App\Enums\InvoiceType;
use App\Exceptions\InvoiceException;
use App\Http\Controllers\Controller;
use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;
use Inertia\Inertia;

class WithdrawalController extends Controller
{
    public function index()
    {
        $invoices = Invoice::query()
            ->with('wallet.user')
            ->where('type', InvoiceType::WITHDRAWAL)
            ->orderByDesc('id')
            ->paginate(10);

        $invoices = InvoiceResource::collection($invoices);

        return Inertia::render('Withdrawal/Index', compact('invoices'));
    }

    public function success(Invoice $invoice)
    {
        try {
            services()->invoice()->finishWithdrawal($invoice);
        } catch (InvoiceException $e) {}
    }

    public function fail(Invoice $invoice)
    {
        try {
            services()->invoice()->cancelWithdrawal($invoice);
        } catch (InvoiceException $e) {}
    }
}
