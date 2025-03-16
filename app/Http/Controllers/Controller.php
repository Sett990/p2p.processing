<?php

namespace App\Http\Controllers;

use App\Enums\DisputeStatus;
use App\Enums\InvoiceStatus;
use App\Enums\OrderStatus;
use App\ObjectValues\TableFilters\DateRange;
use App\ObjectValues\TableFilters\TableFiltersValue;
use Carbon\Carbon;

abstract class Controller
{
    public function getTableFilters(): TableFiltersValue
    {
        $orderStatuses = request()->input('filters.orderStatuses', '');
        $orderStatuses = explode(',', $orderStatuses);

        foreach ($orderStatuses as $key => $value) {
            if (! OrderStatus::tryFrom($value)) {
                unset($orderStatuses[$key]);
            }
        }

        $disputeStatuses = request()->input('filters.disputeStatuses', '');
        $disputeStatuses = explode(',', $disputeStatuses);

        foreach ($disputeStatuses as $key => $value) {
            if (! DisputeStatus::tryFrom($value)) {
                unset($disputeStatuses[$key]);
            }
        }

        $invoiceStatuses = request()->input('filters.invoiceStatuses', '');
        $invoiceStatuses = explode(',', $invoiceStatuses);

        foreach ($invoiceStatuses as $key => $value) {
            if (! InvoiceStatus::tryFrom($value)) {
                unset($invoiceStatuses[$key]);
            }
        }

        $orderStatuses = request()->input('filters.orderStatuses', '');
        $orderStatuses = explode(',', $orderStatuses);

        foreach ($orderStatuses as $key => $value) {
            if (! OrderStatus::tryFrom($value)) {
                unset($orderStatuses[$key]);
            }
        }

        $startDate = request()->input('filters.dateRange.startDate');
        if ($startDate) {
            $startDate = Carbon::createFromFormat('d/m/Y', $startDate);
        }

        $endDate = request()->input('filters.dateRange.endDate');
        if ($endDate) {
            $endDate = Carbon::createFromFormat('d/m/Y', $endDate);
        }

        if ($startDate && $endDate?->lessThan($startDate)) {
            $endDate = null;
        }

        $externalID = request()->input('filters.externalID');
        $uuid = request()->input('filters.uuid');

        $currentFilters = [
            'dateRange' => [
                'startDate' => $startDate,
                'endDate' => $endDate,
            ],
            'orderStatuses' => array_values($orderStatuses),
            'disputeStatuses' => array_values($disputeStatuses),
            'invoiceStatuses' => array_values($invoiceStatuses),
            'externalID' => $externalID,
            'uuid' => $uuid,
            'search' => request()->input('filters.search'),
            'onlySuccessParsing' => (bool) request()->input('filters.onlySuccessParsing'),
            'amount' => request()->input('filters.amount'),
            'paymentDetail' => request()->input('filters.paymentDetail'),
            'user' => request()->input('filters.user'),
            'id' => request()->input('filters.id'),
            'name' => request()->input('filters.name'),
            'active' => (bool) request()->input('filters.active'),
            'multipliedDetails' => (bool) request()->input('filters.multipliedDetails'),
            'online' => (bool) request()->input('filters.online'),
            'address' => request()->input('filters.address'),
            'merchant' => request()->input('filters.merchant'),
            'currency' => request()->input('filters.currency'),
            'method' => request()->input('filters.method'),
            'status' => request()->has('filters.status') ? (bool) request()->input('filters.status') : null,
        ];

        return new TableFiltersValue(
            dateRange: new DateRange(...$currentFilters['dateRange']),
            orderStatuses: $currentFilters['orderStatuses'],
            disputeStatuses: $currentFilters['disputeStatuses'],
            invoiceStatuses: $currentFilters['invoiceStatuses'],
            externalID: $currentFilters['externalID'],
            uuid: $currentFilters['uuid'],
            search: $currentFilters['search'],
            onlySuccessParsing: $currentFilters['onlySuccessParsing'],
            amount: $currentFilters['amount'],
            paymentDetail: $currentFilters['paymentDetail'],
            user: $currentFilters['user'],
            id: $currentFilters['id'],
            name: $currentFilters['name'],
            active: $currentFilters['active'],
            multipliedDetails: $currentFilters['multipliedDetails'],
            online: $currentFilters['online'],
            address: $currentFilters['address'],
            merchant: $currentFilters['merchant'],
            currency: $currentFilters['currency'],
            method: $currentFilters['method'],
            status: $currentFilters['status'],
        );
    }

    public function getFiltersData(): array
    {
        $orderStatuses = [];
        foreach (OrderStatus::values() as $status) {
            $orderStatuses[] = [
                'name' => trans("order.status.{$status}"),
                'value' => $status,
            ];
        }

        $disputeStatuses = [];
        foreach (DisputeStatus::values() as $status) {
            $disputeStatuses[] = [
                'name' => trans("dispute.status.{$status}"),
                'value' => $status,
            ];
        }

        $invoiceStatuses = [];
        foreach (InvoiceStatus::values() as $status) {
            $invoiceStatuses[] = [
                'name' => trans("invoice.status.{$status}"),
                'value' => $status,
            ];
        }

        return [
            'orderStatuses' => $orderStatuses,
            'disputeStatuses' => $disputeStatuses,
            'invoiceStatuses' => $invoiceStatuses,
        ];
    }
}
