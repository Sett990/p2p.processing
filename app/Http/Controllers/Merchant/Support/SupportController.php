<?php

namespace App\Http\Controllers\Merchant\Support;

use App\Http\Controllers\Controller;
use App\Http\Requests\Merchant\Support\StoreSupportRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Utils\Transaction;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;

class SupportController extends Controller
{
    public function index()
    {
        $merchant = auth()->user();

        // Получаем всех саппортов текущего мерчанта
        $supports = User::query()
            ->with('roles')
            ->whereHas('roles', function ($query) {
                $query->where('name', 'Merchant Support');
            })
            ->where('merchant_id', $merchant->id)
            ->orderByDesc('id')
            ->paginate(10);

        $supports = UserResource::collection($supports);

        return Inertia::render('Merchant/Support/Index', compact('supports'));
    }

    public function create()
    {
        return Inertia::render('Merchant/Support/Create');
    }

    public function store(StoreSupportRequest $request)
    {
        Transaction::run(function () use ($request) {
            $merchant = auth()->user();
            $merchantSupportRole = Role::where('name', 'Merchant Support')->first();

            if (!$merchantSupportRole) {
                return redirect()->back()->with('error', 'Роль Merchant Support не найдена');
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'apk_access_token' => strtolower(Str::random(32)),
                'api_access_token' => strtolower(Str::random(32)),
                'avatar_uuid' => $request->email,
                'avatar_style' => 'adventurer',
                'merchant_id' => $merchant->id, // Привязываем саппорта к мерчанту
                'traffic_enabled_at' => now(),
            ]);

            $user->assignRole($merchantSupportRole);

            services()->wallet()->create($user);
        });

        return redirect()->route('merchant.support.index');
    }

    public function edit(User $support)
    {
        // Проверяем, что саппорт принадлежит текущему мерчанту
        $this->checkSupportOwnership($support);
        
        $support->load('roles');
        $support = UserResource::make($support)->resolve();
        
        return Inertia::render('Merchant/Support/Edit', compact('support'));
    }
    
    public function update(Request $request, User $support)
    {
        // Проверяем, что саппорт принадлежит текущему мерчанту
        $this->checkSupportOwnership($support);
        
        $request->validate([
            'name' => 'required|string|min:3|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:users,email,' . $support->id,
        ]);
        
        Transaction::run(function () use ($request, $support) {
            $support->update([
                'name' => $request->name,
                'email' => $request->email,
                'banned_at' => $request->banned ? now() : null,
            ]);
        });
        
        return redirect()->route('merchant.support.index');
    }
    
    /**
     * Проверка, что саппорт принадлежит текущему мерчанту
     */
    private function checkSupportOwnership(User $support)
    {
        $merchant = auth()->user();
        
        if ($support->merchant_id !== $merchant->id || !$support->hasRole('Merchant Support')) {
            abort(403, 'У вас нет прав на управление этим саппортом');
        }
    }
}
