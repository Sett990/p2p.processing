<?php

namespace App\Http\Controllers\MerchantSupport;

use App\Http\Controllers\Controller;
use App\Models\User;
use Inertia\Inertia;

class IntegrationController extends Controller
{
    public function index()
    {
        /**
         * @var User $user
         */
        $user = auth()->user();
        $token = $user->merchant->api_access_token;

        return Inertia::render('MerchantSupport/Integration/Index', compact('token'));
    }
}
