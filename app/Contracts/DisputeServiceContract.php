<?php

namespace App\Contracts;

use App\Exceptions\DisputeException;
use App\Models\Dispute;
use Illuminate\Http\UploadedFile;

interface DisputeServiceContract
{
    /**
     * @throws DisputeException
     */
    public function create(int $orderID, ?UploadedFile $receipt = null): Dispute;

    public function accept(int $disputeID): bool;

    public function cancel(int $disputeID, string $reason): bool;

    public function rollback(int $disputeID): bool;
}
