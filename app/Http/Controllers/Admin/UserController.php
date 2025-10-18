<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\User\StoreRequest;
use App\Http\Requests\Admin\User\UpdateRequest;
use App\DTO\User\UserCreateDTO;
use App\DTO\User\UserUpdateDTO;
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
            ->when(!empty($filters->roles), function ($query) use ($filters) {
                $query->whereHas('roles', function ($q) use ($filters) {
                    $q->whereIn('name', $filters->roles);
                });
            })
            ->orderByDesc('id')
            ->paginate(request()->per_page ?? 10);

        $users = UserResource::collection($users);

        // Получаем данные для фильтров
        $filtersVariants = $this->getFiltersData();

        return Inertia::render('User/Index', compact('users', 'filters', 'filtersVariants'));
    }

    public function create()
    {
        $roles = Role::where('name', '!=', 'Merchant Support')->get();

        return Inertia::render('User/Create', compact('roles'));
    }

    public function store(StoreRequest $request)
    {
        $dto = UserCreateDTO::makeFromRequest($request->validated());
        services()->user()->create($dto);

        return redirect()->route('admin.users.index');
    }

    public function edit(User $user)
    {
        $user->load('roles', 'meta', 'promoCode');
        $roles = Role::where('name', '!=', 'Merchant Support')->get();

        $user = UserResource::make($user)->resolve();

        return Inertia::render('User/Edit', compact('user', 'roles'));
    }

    public function update(UpdateRequest $request, User $user)
    {
        $dto = UserUpdateDTO::makeFromRequest($request->validated());
        services()->user()->update($dto, $user);

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
