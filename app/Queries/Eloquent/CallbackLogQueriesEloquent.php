<?php

namespace App\Queries\Eloquent;

use App\Models\CallbackLog;
use App\ObjectValues\TableFilters\TableFiltersValue;
use App\Queries\Interfaces\CallbackLogQueries;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CallbackLogQueriesEloquent implements CallbackLogQueries
{
    /**
     * Получить пагинированный список логов колбеков для админки
     *
     * @param TableFiltersValue $filters
     * @return LengthAwarePaginator
     */
    public function paginateForAdmin(TableFiltersValue $filters): LengthAwarePaginator
    {
        return CallbackLog::query()
            ->with('callbackable')
            ->when($filters->uuid, function ($query) use ($filters) {
                $query->whereHasMorph('callbackable', '*', function ($q) use ($filters) {
                    $q->where('uuid', 'LIKE', '%' . $filters->uuid . '%');
                });
            })
            ->orderByDesc('id')
            ->paginate(request()->per_page ?? 10);
    }
} 