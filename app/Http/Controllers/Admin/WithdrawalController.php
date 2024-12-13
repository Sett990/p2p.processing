<?php

namespace App\Http\Controllers\Admin;

use App\Enums\InvoiceStatus;
use App\Enums\InvoiceType;
use App\Enums\InvoiceWithdrawalSourceType;
use App\Enums\TransactionType;
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
        if ($invoice->type->notEquals(InvoiceType::WITHDRAWAL) || $invoice->status->notEquals(InvoiceStatus::PENDING)) {
            return;
        }

        $invoice->update(['status' => InvoiceStatus::SUCCESS]);
    }

    public function fail(Invoice $invoice)
    {
        if ($invoice->type->notEquals(InvoiceType::WITHDRAWAL) || $invoice->status->notEquals(InvoiceStatus::PENDING)) {
            return;
        }

        $invoice->update(['status' => InvoiceStatus::FAIL]);

        if ($invoice->source_type->equals(InvoiceWithdrawalSourceType::TRUST)) {
            services()->wallet()->giveToTrust($invoice->wallet, $invoice->amount, TransactionType::ROLLBACK_FOR_USER_WITHDRAWAL);
        } else if ($invoice->source_type->equals(InvoiceWithdrawalSourceType::MERCHANT)) {
            services()->wallet()->giveToMerchant($invoice->wallet, $invoice->amount);
        }
    }
}
