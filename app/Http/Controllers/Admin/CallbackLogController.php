<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\CallbackLogResource;
use Inertia\Inertia;

class CallbackLogController extends Controller
{
    /**
     * Отображает список логов колбеков
     */
    public function index()
    {
        $logs = queries()->callbackLog()->paginateForAdmin();

        return Inertia::render('CallbackLogs/Index', [
            'logs' => CallbackLogResource::collection($logs),
        ]);
    }
}
