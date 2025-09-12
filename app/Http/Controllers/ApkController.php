<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class ApkController extends Controller
{
    public function download()
    {
        return response()->file(base_path('/p2p-bridge.apk') ,[
            'Content-Type'=>'application/vnd.android.package-archive',
        ]) ;
    }
}
