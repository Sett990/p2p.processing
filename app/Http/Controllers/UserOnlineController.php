<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserOnlineController extends Controller
{
    public function toggle(Request $request)
    {
        $user = $request->user();

        //$user->hasRole('Super Admin') || $user->hasRole('Trader');

        $user->update(['is_online' => !$user->is_online]);
    }
}
