<?php

namespace App\ObjectValues\TableFilters;

use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;

class DateRange implements Arrayable

{
    public function __construct(
        public ?Carbon $startDate = null,
        public ?Carbon $endDate = null,
    )
    {}

    public function toArray(): array
    {
        return [
            'startDate' => $this->startDate?->format('d/m/Y'),
            'endDate' => $this->endDate?->format('d/m/Y'),
        ];
    }
}
