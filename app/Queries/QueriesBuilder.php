<?php

namespace App\Queries;

use App\Contracts\QueriesBuilderContract;
use App\Queries\Interfaces\DisputeQueries;
use App\Queries\Interfaces\InvoiceQueries;
use App\Queries\Interfaces\MerchantQueries;
use App\Queries\Interfaces\OrderQueries;
use App\Queries\Interfaces\PaymentDetailQueries;
use App\Queries\Interfaces\PaymentGatewayQueries;

class QueriesBuilder implements QueriesBuilderContract
{
    public function order(): OrderQueries
    {
        return make(OrderQueries::class);
    }

    public function paymentGateway(): PaymentGatewayQueries
    {
        return make(PaymentGatewayQueries::class);
    }

    public function paymentDetail(): PaymentDetailQueries
    {
        return make(PaymentDetailQueries::class);
    }

    public function dispute(): DisputeQueries
    {
        return make(DisputeQueries::class);
    }

    public function merchant(): MerchantQueries
    {
        return make(MerchantQueries::class);
    }

    public function invoice(): InvoiceQueries
    {
        return make(InvoiceQueries::class);
    }
}
