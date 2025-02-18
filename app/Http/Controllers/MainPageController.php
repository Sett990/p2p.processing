<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class MainPageController extends Controller
{
    public function merchant()
    {
        return Inertia::render('MainPage/Merchant/Index');
    }
}
