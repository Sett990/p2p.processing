<?php

namespace App\Queries\Interfaces;

use App\Models\Dispute;
use App\Models\Order;
use App\Models\User;
use App\Services\Money\Money;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface OrderQueries
{
    public function findPending(Money $amount, User $user): ?Order;

    public function paginateForAdmin(array $statuses = [], ?Carbon $startDate = null, ?Carbon $endDate = null, ?string $externalID = null, ?string $uuid = null): LengthAwarePaginator;

    public function paginateForUser(User $user, array $statuses = [], ?Carbon $startDate = null, ?Carbon $endDate = null, ?string $uuid = null): LengthAwarePaginator;

    public function paginateForMerchant(User $user, array $statuses = [], ?string $externalID = null, ?string $uuid = null): LengthAwarePaginator;

    /**
     * @return Collection<int, Dispute>
     */
    public function getForAdminApiDisputeCreate(): Collection;
}
