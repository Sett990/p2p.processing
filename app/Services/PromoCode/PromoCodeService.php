<?php

declare(strict_types=1);

namespace App\Services\PromoCode;

use App\Contracts\PromoCodeServiceContract;
use App\DTO\PromoCode\PromoCodeCreateDTO;
use App\Models\PromoCode;
use Illuminate\Support\Str;

class PromoCodeService implements PromoCodeServiceContract
{
    public function create(PromoCodeCreateDTO $dto): PromoCode
    {
        // Если код пустой, сгенерировать автоматически
        $code = trim($dto->code);
        if ($code === '') {
            $code = Str::upper(Str::random(8));
        }

        // Создание
        return PromoCode::create([
            'team_leader_id' => $dto->team_leader_id,
            'code' => $code,
            'max_uses' => $dto->max_uses,
            'used_count' => 0,
            'is_active' => $dto->is_active,
        ]);
    }
}


