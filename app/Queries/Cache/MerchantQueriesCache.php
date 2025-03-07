<?php

namespace App\Queries\Cache;

use App\Queries\Interfaces\MerchantQueries;
use App\Queries\Eloquent\MerchantQueriesEloquent;
use App\Models\Merchant;
use Illuminate\Support\Facades\Cache;

class MerchantQueriesCache implements MerchantQueries
{
    private MerchantQueriesEloquent $eloquentQueries;
    private int $cacheTtl;

    public function __construct(MerchantQueriesEloquent $eloquentQueries, int $cacheTtl = 3600)
    {
        $this->eloquentQueries = $eloquentQueries;
        $this->cacheTtl = $cacheTtl;
    }

    public function findByUUID(string $uuid): ?Merchant
    {
        $cacheKey = "merchant_by_uuid_{$uuid}";
        
        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($uuid) {
            return $this->eloquentQueries->findByUUID($uuid);
        });
    }
}
