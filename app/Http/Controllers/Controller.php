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

        $apiLogStatuses = request()->input('filters.apiLogStatuses', '');
        $apiLogStatuses = explode(',', $apiLogStatuses);

        foreach ($apiLogStatuses as $key => $value) {
            if (! in_array($value, [0, 1])) {
                unset($apiLogStatuses[$key]);
            }
        }

        $orderStatuses = request()->input('filters.orderStatuses', '');
        $orderStatuses = explode(',', $orderStatuses);

        foreach ($orderStatuses as $key => $value) {
            if (! OrderStatus::tryFrom($value)) {
                unset($orderStatuses[$key]);
            }
        }

        $roles = request()->input('filters.roles', '');
        $roles = explode(',', $roles);

        $roles = array_filter($roles);

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
            'orderStatuses' => $orderStatuses,
            'disputeStatuses' => $disputeStatuses,
            'invoiceStatuses' => $invoiceStatuses,
            'apiLogStatuses' => $apiLogStatuses,
            'dateRange' => [
                'startDate' => $startDate,
                'endDate' => $endDate,
            ],
            'externalID' => $externalID,
            'uuid' => $uuid,
            'search' => request()->input('filters.search'),
            'onlySuccessParsing' => request()->input('filters.onlySuccessParsing') === 'true',
            'amount' => request()->input('filters.amount'),
            'paymentDetail' => request()->input('filters.paymentDetail'),
            'user' => request()->input('filters.user'),
            'id' => request()->input('filters.id'),
            'name' => request()->input('filters.name'),
            'active' => request()->input('filters.active') === 'true',
            'multipliedDetails' => request()->input('filters.multipliedDetails') === 'true',
            'online' => request()->input('filters.online') === 'true',
            'address' => request()->input('filters.address'),
            'merchant' => request()->input('filters.merchant'),
            'currency' => request()->input('filters.currency'),
            'method' => request()->input('filters.method'),
            'traffic_disabled' => request()->input('filters.traffic_disabled') === 'true',
            'roles' => $roles,
        ];

        return new TableFiltersValue(
            dateRange: new DateRange(...$currentFilters['dateRange']),
            orderStatuses: $currentFilters['orderStatuses'],
            disputeStatuses: $currentFilters['disputeStatuses'],
            invoiceStatuses: $currentFilters['invoiceStatuses'],
            apiLogStatuses: $currentFilters['apiLogStatuses'],
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
            traffic_disabled: $currentFilters['traffic_disabled'],
            roles: $currentFilters['roles'],
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

        $apiLogStatuses = [
            [
                'name' => 'Успешные',
                'value' => '1',
            ],
            [
                'name' => 'Неуспешные',
                'value' => '0',
            ],
        ];

        // Получаем список всех ролей из БД
        $roles = \Spatie\Permission\Models\Role::all()
            ->map(function ($role) {
                return [
                    'name' => $role->name,
                    'value' => $role->name,
                ];
            })
            ->toArray();

        return [
            'orderStatuses' => $orderStatuses,
            'disputeStatuses' => $disputeStatuses,
            'invoiceStatuses' => $invoiceStatuses,
            'apiLogStatuses' => $apiLogStatuses,
            'roles' => $roles,
        ];
    }
}
