<?php

namespace App\Queries\Interfaces;

use App\Models\Dispute;
use App\Models\Order;
use App\Models\PaymentGateway;
use App\Models\User;
use App\Services\Money\Money;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface OrderQueries
{
    public function findPendingForSBP(Money $amount, User $user, PaymentGateway $paymentGateway): ?Order;

    public function findPending(Money $amount, User $user, PaymentGateway $paymentGateway): ?Order;

    public function paginateForAdmin(): LengthAwarePaginator;

    public function paginateForUser(User $user): LengthAwarePaginator;

    public function paginateForMerchant(User $user): LengthAwarePaginator;

    /**
     * @return Collection<int, Dispute>
     */
    public function getForAdminApiDisputeCreate(): Collection;
}
