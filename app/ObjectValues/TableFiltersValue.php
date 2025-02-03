<?php

namespace App\ObjectValues;

use Illuminate\Contracts\Support\Arrayable;

class TableFiltersValue implements Arrayable
{
    public function __construct(
        public array $orderStatuses = [],
        public ?string $startDate = null,
        public ?string $endDate = null,
        public ?string $externalID = null,
        public ?string $uuid = null,
    )
    {}

    public function toArray(): array
    {
        $data = get_object_vars($this);

        $data['orderStatuses'] = implode(',', $data["orderStatuses"]);

        return $data;
    }
}
