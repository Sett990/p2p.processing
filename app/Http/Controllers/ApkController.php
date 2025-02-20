<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class ApkController extends Controller
{
    public function index()
    {
        $token =  auth()->user()->apk_access_token;

        return Inertia::render('APK/Index', compact('token'));
    }

    public function download()
    {
        return response()->file(base_path('/sms-app.apk') ,[
            'Content-Type'=>'application/vnd.android.package-archive',
        ]) ;
    }
}
