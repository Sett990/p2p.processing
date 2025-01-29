<?php

namespace App\Http\Controllers\API\APP;

use App\Contracts\SmsServiceContract;
use App\DTO\SMS\SmsDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\SMS\StoreRequest;
use App\Jobs\HandleSmsJob;
use App\Models\User;

class SmsController extends Controller
{
    public function store(StoreRequest $request)
    {
        $user = User::where('apk_access_token', $request->header('Access-Token'))->first();

        if (! $user) {
            return response()->failWithMessage('Invalid access token');
        }

        HandleSmsJob::dispatch(
            SmsDTO::fromArray($request->validated() + [
                    'user' => $user
                ])
        );

        return response()->success();
    }
}
