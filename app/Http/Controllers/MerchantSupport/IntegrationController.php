<?php

namespace App\Http\Controllers\MerchantSupport;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class IntegrationController extends Controller
{
    public function index()
    {
        $token = auth()->user()->api_access_token;

        return Inertia::render('MerchantSupport/Integration/Index', compact('token'));
    }
} 