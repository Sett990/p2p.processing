<?php

namespace App\ObjectValues\TableFilters;

use Illuminate\Contracts\Support\Arrayable;

class TableFiltersValue implements Arrayable
{
    public function __construct(
        public DateRange $dateRange,
        public array $orderStatuses = [],
        public array $disputeStatuses = [],
        public array $invoiceStatuses = [],
        public array $apiLogStatuses = [],
        public ?string $externalID = null,
        public ?string $uuid = null,
        public ?string $search = null,
        public bool $onlySuccessParsing = false,
        public ?string $amount = null,
        public ?string $paymentDetail = null,
        public ?string $user = null,
        public ?string $id = null,
        public ?string $name = null,
        public bool $active = false,
        public bool $multipliedDetails = false,
        public bool $online = false,
        public ?string $address = null,
        public ?string $merchant = null,
        public ?string $currency = null,
        public ?string $method = null,
        public ?bool $status = null,
        public bool $traffic_disabled = false,
        public array $roles = [],
    )
    {}

    public function toArray(): array
    {
        return [
            'dateRange' => $this->dateRange->toArray(),
            'orderStatuses' => implode(',', $this->orderStatuses),
            'disputeStatuses' => implode(',', $this->disputeStatuses),
            'invoiceStatuses' => implode(',', $this->invoiceStatuses),
            'apiLogStatuses' => implode(',', $this->apiLogStatuses),
            'externalID' => $this->externalID,
            'uuid' => $this->uuid,
            'search' => $this->search,
            'onlySuccessParsing' => $this->onlySuccessParsing,
            'amount' => $this->amount,
            'paymentDetail' => $this->paymentDetail,
            'user' => $this->user,
            'id' => $this->id,
            'name' => $this->name,
            'active' => $this->active,
            'multipliedDetails' => $this->multipliedDetails,
            'online' => $this->online,
            'address' => $this->address,
            'merchant' => $this->merchant,
            'currency' => $this->currency,
            'method' => $this->method,
            'status' => $this->status,
            'traffic_disabled' => $this->traffic_disabled,
            'roles' => implode(',', $this->roles),
        ];
    }
}
