<?php

namespace App\Http\Controllers\Support;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index()
    {
        $filters = $this->getTableFilters();

        $users = User::query()
            ->with(['roles', 'wallet'])
            ->when($filters->user, function ($query) use ($filters) {
                $query->where(function ($query) use ($filters) {
                    $query->where('email', 'like', '%' . $filters->user . '%');
                    $query->orWhere('name', 'like', '%' . $filters->user . '%');
                });
            })
            ->when($filters->online, function ($query) use ($filters) {
                $query->where('is_online', true);
            })
            ->when($filters->traffic_disabled, function ($query) use ($filters) {
                $query->where('stop_traffic', true);
            })
            ->orderByDesc('id')
            ->paginate(10);

        $users = UserResource::collection($users);

        return Inertia::render('Support/User/Index', compact('users', 'filters'));
    }
    
    public function toggleTraffic(Request $request, User $user)
    {
        $user->update([
            'stop_traffic' => !$user->stop_traffic
        ]);
        
        return redirect()->back();
    }
} 