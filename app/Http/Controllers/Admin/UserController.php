<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\User\StoreRequest;
use App\Http\Requests\Admin\User\UpdateRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index()
    {
        $filters = $this->getTableFilters();

        $users = User::query()
            ->with(['roles', 'wallet'])
            ->when($filters->user, function ($query) use ($filters) {
                $query->where('email', 'like', '%' . $filters->user . '%');
                $query->orWhere('name', 'like', '%' . $filters->user . '%');
            })
            ->orderByDesc('id')
            ->paginate(10);

        $users = UserResource::collection($users);

        return Inertia::render('User/Index', compact('users', 'filters'));
    }

    public function create()
    {
        $roles = Role::all();

        return Inertia::render('User/Create', compact('roles'));
    }

    public function store(StoreRequest $request)
    {
        DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'apk_access_token' => strtolower(Str::random(32)),
                'api_access_token' => strtolower(Str::random(32)),
            ]);

            $user->assignRole($request->role_id);

            services()->wallet()->create($user);
        });

        return redirect()->route('admin.users.index');
    }

    public function edit(User $user)
    {
        $user->load('roles', 'meta');
        $roles = Role::all();

        $user = UserResource::make($user)->resolve();

        return Inertia::render('User/Edit', compact('user', 'roles'));
    }

    public function update(UpdateRequest $request, User $user)
    {
        DB::transaction(function () use ($request, $user) {
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'banned_at' => $request->banned ? now() : null,
                'payouts_enabled' => $request->payouts_enabled,
            ]);
            if ($user->id !== 1) {
                $user->syncRoles($request->role_id);
            }

            if ($user->banned_at) {
                $user->paymentDetails()->update([
                    'is_active' => false
                ]);
            }

            $user->meta->update([
                'order_service_commission_rate' => $request->order_service_commission_rate,
                'payout_service_commission_rate' => $request->payout_service_commission_rate,
            ]);
        });

        return redirect()->route('admin.users.index');
    }

    public function toggleOnline(Request $request, User $user)
    {
        if ((int)$user->is_online !== (int)$request->is_online) {
            $user->update(['is_online' => !$user->is_online]);
        }
        if ((int)$user->is_payout_online !== (int)$request->is_payout_online) {
            services()->payout()->toggleTraderOffersActivity($user);
        }
    }
}
