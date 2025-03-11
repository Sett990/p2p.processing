<?php

namespace App\Http\Controllers\TeamLeader;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PromoCodeController extends Controller
{
    public function index()
    {
        return Inertia::render('PromoCode/Index');
    }
}
