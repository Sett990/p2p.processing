<?php

namespace App\DTO\User;

use App\DTO\BaseDTO;

readonly class UserUpdateDTO extends BaseDTO
{
    public function __construct(
        public string $login,
        public int $role_id,
        public ?bool $banned = null,
        public bool $stop_traffic = false,
        public bool $can_work_without_device = false,
        public bool $is_vip = false,
        public ?int $referral_commission_percentage = null,
        public ?int $reserve_balance_limit = null,
        public ?string $promo_code = null,
    ) {}

    public static function makeFromRequest(array $data): static
    {
        return new static(
            login: strtolower($data['login']),
            banned: isset($data['banned']) ? (bool) $data['banned'] : null,
            stop_traffic: (bool) ($data['stop_traffic'] ?? false),
            can_work_without_device: (bool) ($data['can_work_without_device'] ?? false),
            is_vip: (bool) ($data['is_vip'] ?? false),
            referral_commission_percentage: isset($data['referral_commission_percentage']) ? (int) $data['referral_commission_percentage'] : null,
            reserve_balance_limit: isset($data['reserve_balance_limit']) ? (int) $data['reserve_balance_limit'] : null,
            role_id: (int) $data['role_id'],
            promo_code: $data['promo_code'] ?? null,
        );
    }
}


