<?php

namespace App\Http\Controllers\Trader;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StatisticController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('Statistic/Trader/Index');
    }
}
