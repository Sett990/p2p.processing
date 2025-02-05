<?php

namespace App\Http\Controllers;

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
        ];

        return new TableFiltersValue(
            dateRange: new DateRange(...$currentFilters['dateRange']),
            orderStatuses: $currentFilters['orderStatuses'],
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

        return [
            'orderStatuses' => $orderStatuses,
        ];
    }
}
