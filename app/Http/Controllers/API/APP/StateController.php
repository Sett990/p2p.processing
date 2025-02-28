<?php

namespace App\Http\Controllers\API\APP;

use App\Enums\DisputeStatus;
use App\Http\Controllers\Controller;
use App\Models\Dispute;
use App\Models\User;
use Illuminate\Http\Request;

class StateController extends Controller
{
    public function index(Request $request)
    {
        $user = cache()->remember('apk_access_token-'.$request->header('Access-Token', ''), 60 * 24, function () use ($request) {
            return User::where('apk_access_token', $request->header('Access-Token'))->first();
        });

        if (! $user) {
            return response()->failWithMessage('Invalid access token');
        }

        cache()->put("user-apk-latest-ping-at-$user->id", now()->toDateTimeString());

        $queryDispute = Dispute::query()
            ->whereRelation('order.paymentDetail', 'user_id', $user->id)
            ->where('status', DisputeStatus::PENDING);

        $cacheKey = "user_{$user->id}_pending_disputes";

        $latestDisputeAt = cache()->remember("latest_$cacheKey", now()->addSeconds(10), function () use ($queryDispute) {
            return $queryDispute->clone()
                ->latest('created_at')
                ->first('created_at')
                ?->created_at
                ?->timestamp;
        });

        $oldestDisputeAt = cache()->remember("oldest_$cacheKey", now()->addSeconds(10), function () use ($queryDispute) {
            return $queryDispute->clone()
                ->oldest('created_at')
                ->first('created_at')
                ?->created_at
                ?->timestamp;
        });

        $disputeCount = cache()->remember("count_$cacheKey", now()->addSeconds(10), function () use ($queryDispute) {
            return $queryDispute->clone()->count();
        });

        return [
            'pending_disputes' => [
                'latest_at' => $latestDisputeAt,
                'oldest_at' => $oldestDisputeAt,
                'count' => $disputeCount,
            ]
        ];
    }
}
