<?php

namespace App\Queries\Interfaces;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CallbackLogQueries
{
    /**
     * Получить пагинированный список логов колбеков для админки
     *
     * @return LengthAwarePaginator
     */
    public function paginateForAdmin(): LengthAwarePaginator;
} 