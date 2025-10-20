<?php

declare(strict_types=1);

namespace App\Contracts;

use App\DTO\PromoCode\PromoCodeCreateDTO;
use App\Models\PromoCode;

interface PromoCodeServiceContract
{
    /**
     * Создать промокод.
     */
    public function create(PromoCodeCreateDTO $dto): PromoCode;
}


