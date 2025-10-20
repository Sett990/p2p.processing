<?php

declare(strict_types=1);

namespace App\DTO\PromoCode;

use App\DTO\BaseDTO;

readonly class PromoCodeCreateDTO extends BaseDTO
{
    public function __construct(
        public int $team_leader_id,
        public string $code,
        public int $max_uses,
        public bool $is_active,
    ) {}
}


