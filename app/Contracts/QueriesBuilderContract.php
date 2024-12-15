<?php

namespace App\Contracts;

use App\Queries\Interfaces\DisputeQueries;
use App\Queries\Interfaces\InvoiceQueries;
use App\Queries\Interfaces\MerchantQueries;
use App\Queries\Interfaces\OrderQueries;
use App\Queries\Interfaces\PaymentDetailQueries;
use App\Queries\Interfaces\PaymentGatewayQueries;

interface QueriesBuilderContract
{
    public function order(): OrderQueries;

    public function paymentGateway(): PaymentGatewayQueries;

    public function paymentDetail(): PaymentDetailQueries;

    public function dispute(): DisputeQueries;

    public function merchant(): MerchantQueries;

    public function invoice(): InvoiceQueries;
}
