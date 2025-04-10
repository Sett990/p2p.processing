<?php

namespace App\Queries\Eloquent;

use App\Models\CallbackLog;
use App\Queries\Interfaces\CallbackLogQueries;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CallbackLogQueriesEloquent implements CallbackLogQueries
{
    /**
     * Получить пагинированный список логов колбеков для админки
     *
     * @return LengthAwarePaginator
     */
    public function paginateForAdmin(): LengthAwarePaginator
    {
        return CallbackLog::query()
            ->with('callbackable')
            ->orderByDesc('id')
            ->paginate(request()->per_page ?? 10);
    }
} 