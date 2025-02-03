<?php

namespace App\Queries\Interfaces;

use App\Models\Dispute;
use App\Models\Order;
use App\Models\User;
use App\ObjectValues\TableFilters\TableFiltersValue;
use App\Services\Money\Money;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface OrderQueries
{
    public function findPending(Money $amount, User $user): ?Order;

    public function paginateForAdmin(TableFiltersValue $filters): LengthAwarePaginator;

    public function paginateForUser(User $user, TableFiltersValue $filters): LengthAwarePaginator;

    public function paginateForMerchant(User $user, TableFiltersValue $filters): LengthAwarePaginator;

    /**
     * @return Collection<int, Dispute>
     */
    public function getForAdminApiDisputeCreate(): Collection;
}
