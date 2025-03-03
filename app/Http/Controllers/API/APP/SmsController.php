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
        $user = auth()->user();

        cache()->put("user-apk-latest-ping-at-$user->id", now()->toDateTimeString());

        HandleSmsJob::dispatch(
            SmsDTO::fromArray($request->validated() + [
                    'user' => $user
                ])
        );

        return response()->success();
    }
}
