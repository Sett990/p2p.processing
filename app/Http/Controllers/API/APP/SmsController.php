<?php

namespace App\Http\Controllers\API\APP;

use App\DTO\SMS\SmsDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\SMS\StoreRequest;
use App\Jobs\HandleSmsJob;
use App\Models\User;

class SmsController extends Controller
{
    public function store(StoreRequest $request)
    {
        $user = cache()->remember('', 60 * 24, function () use ($request) {
            return User::where('apk_access_token', $request->header('Access-Token'))->first();
        });

        if (! $user) {
            return response()->failWithMessage('Invalid access token');
        }

        cache()->put("user-apk-latest-ping-at-$user->id", now()->toDateTimeString());

        HandleSmsJob::dispatch(
            SmsDTO::fromArray($request->validated() + [
                    'user' => $user
                ])
        );

        return response()->success();
    }
}
