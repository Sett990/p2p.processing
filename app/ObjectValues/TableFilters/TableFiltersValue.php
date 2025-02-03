<?php

namespace App\ObjectValues\TableFilters;

use Illuminate\Contracts\Support\Arrayable;

class TableFiltersValue implements Arrayable
{
    public function __construct(
        public DateRange $dateRange,
        public array $orderStatuses = [],
        public ?string $externalID = null,
        public ?string $uuid = null,
    )
    {}

    public function toArray(): array
    {
        return [
            'dateRange' => $this->dateRange->toArray(),
            'orderStatuses' => implode(',', $this->orderStatuses),
            'externalID' => $this->externalID,
            'uuid' => $this->uuid,
        ];
    }
}
