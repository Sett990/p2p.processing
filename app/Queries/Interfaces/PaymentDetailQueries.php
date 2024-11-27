<?php

namespace App\Queries\Interfaces;

use App\Enums\DetailType;
use App\Models\PaymentDetail;
use App\Models\User;
use App\Services\Money\Money;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface PaymentDetailQueries
{
    public function paginateForAdmin(): LengthAwarePaginator;

    public function paginateForUser(User $user): LengthAwarePaginator;

    public function getForOrderCreate(Money $amount, Money $amount_usdt, array $payment_gateway_ids, ?int $sub_payment_gateway_id, ?DetailType $payment_detail_type = null): ?PaymentDetail;
}
