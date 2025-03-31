<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\User\StoreRequest;
use App\Http\Requests\Admin\User\UpdateRequest;
use App\Http\Resources\UserResource;
use App\Models\PromoCode;
use App\Models\User;
use App\Utils\Transaction;
use Illuminate\Http\Request;
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

        return Inertia::render('User/Index', compact('users', 'filters'));
    }

    public function create()
    {
        $roles = Role::all();

        return Inertia::render('User/Create', compact('roles'));
    }

    public function store(StoreRequest $request)
    {
        Transaction::run(function () use ($request) {
            $promoCodeId = null;
            $promoUsedAt = null;

            if ($request->promo_code) {
                $promoCode = PromoCode::where('code', $request->promo_code)->first();
                if ($promoCode && $promoCode->canBeUsed()) {
                    $promoCodeId = $promoCode->id;
                    $promoUsedAt = now();
                }
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'apk_access_token' => strtolower(Str::random(32)),
                'api_access_token' => strtolower(Str::random(32)),
                'avatar_uuid' => $request->email,
                'avatar_style' => 'adventurer',
                'promo_code_id' => $promoCodeId,
                'promo_used_at' => $promoUsedAt,
                'traffic_enabled_at' => now(),
            ]);

            $user->assignRole($request->role_id);

            services()->wallet()->create($user);

            if ($promoCodeId && $promoCode) {
                $promoCode->incrementUsedCount();
            }
        });

        return redirect()->route('admin.users.index');
    }

    public function edit(User $user)
    {
        $user->load('roles', 'meta', 'promoCode');
        $roles = Role::all();

        $user = UserResource::make($user)->resolve();

        return Inertia::render('User/Edit', compact('user', 'roles'));
    }

    public function update(UpdateRequest $request, User $user)
    {
        Transaction::run(function () use ($request, $user) {
            // Получаем текущее состояние stop_traffic
            $wasTrafficStopped = $user->stop_traffic;

            $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'banned_at' => $request->banned ? now() : null,
                'payouts_enabled' => $request->payouts_enabled,
                'stop_traffic' => $request->stop_traffic,
                // Если трафик был остановлен, а теперь его включают, устанавливаем время включения
                'traffic_enabled_at' => $wasTrafficStopped && !$request->stop_traffic ? now() : $user->traffic_enabled_at,
            ]);

            if (!$user->promo_code_id && $request->promo_code) {
                $promoCode = PromoCode::where('code', $request->promo_code)->first();
                if ($promoCode && $promoCode->canBeUsed()) {
                    $user->update([
                        'promo_code_id' => $promoCode->id,
                        'promo_used_at' => now(),
                    ]);
                    $promoCode->incrementUsedCount();
                }
            }

            if ($user->id !== 1) {
                $user->syncRoles($request->role_id);
            }

            if ($user->banned_at) {
                $user->paymentDetails()->update([
                    'is_active' => false
                ]);
            }
        });

        return redirect()->route('admin.users.index');
    }

    public function toggleOnline(Request $request, User $user)
    {
        if ((int)$user->is_online !== (int)$request->is_online) {
            if ($user->stop_traffic && (int)$request->is_online) {
                return;
            }

            $user->update(['is_online' => !$user->is_online]);
        }
        if ((int)$user->is_payout_online !== (int)$request->is_payout_online) {
            services()->payout()->toggleTraderOffersActivity($user);
        }
    }

    public function reset2fa(User $user)
    {
        $user->update([
            'google2fa_secret' => null,
        ]);

        return redirect()->back()->with('success', 'Двухфакторная аутентификация успешно сброшена');
    }
}
