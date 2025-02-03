<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\ObjectValues\TableFiltersValue;
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

        $startDate = request()->input('filters.startDate');
        if ($startDate) {
            $startDate = Carbon::createFromFormat('d/m/Y', $startDate);
        }

        $endDate = request()->input('filters.endDate');
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
            'startDate' => $startDate?->format('d/m/Y'),
            'endDate' => $endDate?->format('d/m/Y'),
            'externalID' => $externalID,
            'uuid' => $uuid,
        ];

        return new TableFiltersValue(...$currentFilters);
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
