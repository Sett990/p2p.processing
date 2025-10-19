<?php

declare(strict_types=1);

namespace App\Services\Merchant;

use App\Contracts\MerchantServiceContract;
use App\DTO\Merchant\MerchantCreateDTO;
use App\Enums\MarketEnum;
use App\Models\Merchant;
use Illuminate\Support\Str;

class MerchantService implements MerchantServiceContract
{
    public function create(MerchantCreateDTO $data): Merchant
    {
        return Merchant::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $data->user_id,
            'active' => true,
            'name' => $data->name,
            'description' => (string) ($data->description ?? ''),
            'domain' => $data->project_link ? parse_url($data->project_link)['host'] ?? '' : '',
            'settings' => [],
            'gateway_settings' => [],
            'market' => MarketEnum::BYBIT,
        ]);
    }
}


