<?php

declare(strict_types=1);

namespace App\Services\Cascade;

use App\Contracts\CascadeServiceContract;
use App\DTO\Cascade\CreateCascadeDealDTO;
use App\Enums\OrderStatus;
use App\Enums\OrderSubStatus;
use App\Models\CascadeDeal;
use Illuminate\Support\Str;

/**
 * Центральный сервис каскада.
 *
 * На текущем этапе содержит только контрактный метод создания каскадной сделки
 * (без запуска каскадной логики и провайдеров).
 */
class CascadeService implements CascadeServiceContract
{
    public function createDeal(CreateCascadeDealDTO $dto): CascadeDeal
    {
        return CascadeDeal::create([
            'uuid' => (string) Str::uuid(),
            'external_id' => $dto->externalId,
            'merchant_id' => $dto->merchantId,
            'amount' => $dto->amount,
            'initial_amount' => $dto->amount,
            'currency' => $dto->currency,
            'payment_method' => $dto->paymentMethod,
            'status' => OrderStatus::PENDING,
            'sub_status' => OrderSubStatus::WAITING_FOR_DETAILS_TO_BE_SELECTED,
            'callback_url' => $dto->callbackUrl,
        ]);
    }
}

