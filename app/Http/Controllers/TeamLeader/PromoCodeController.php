<?php

namespace App\Http\Controllers\TeamLeader;

use App\Http\Controllers\Controller;
use App\Http\Resources\PromoCodeResource;
use App\Models\PromoCode;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class PromoCodeController extends Controller
{
    public function index()
    {
        $promoCodes = PromoCode::query()
            ->where('team_leader_id', auth()->user()->id)
            ->latest()
            ->paginate();

        $promoCodes = PromoCodeResource::collection($promoCodes);

        return Inertia::render('PromoCode/Index', ['promoCodes' => $promoCodes]);
    }

    public function create()
    {
        return Inertia::render('PromoCode/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'nullable|string|max:20|unique:promo_codes,code',
            'max_uses' => 'required|integer|min:0',
            'is_active' => 'required|boolean',
        ]);

        $code = $request->input('code');
        if (empty($code)) {
            $code = Str::upper(Str::random(8));
        }

        PromoCode::create([
            'team_leader_id' => auth()->user()->id,
            'code' => $code,
            'max_uses' => $request->input('max_uses'),
            'used_count' => 0,
            'is_active' => $request->input('is_active'),
        ]);

        return redirect()->route('leader.promo-codes.index')
            ->with('message', 'Промокод успешно создан');
    }

    public function edit(PromoCode $promoCode)
    {
        // Проверка, что промокод принадлежит текущему тимлидеру
        if ($promoCode->team_leader_id !== auth()->user()->id) {
            abort(403);
        }

        return Inertia::render('PromoCode/Edit', [
            'promoCode' => new PromoCodeResource($promoCode),
        ]);
    }

    public function update(Request $request, PromoCode $promoCode)
    {
        // Проверка, что промокод принадлежит текущему тимлидеру
        if ($promoCode->team_leader_id !== auth()->user()->id) {
            abort(403);
        }

        $request->validate([
            'max_uses' => 'required|integer|min:0',
            'is_active' => 'required|boolean',
        ]);

        $promoCode->update([
            'max_uses' => $request->input('max_uses'),
            'is_active' => $request->input('is_active'),
        ]);

        return redirect()->route('leader.promo-codes.index')
            ->with('message', 'Промокод успешно обновлен');
    }

    public function destroy(PromoCode $promoCode)
    {
        // Проверка, что промокод принадлежит текущему тимлидеру
        if ($promoCode->team_leader_id !== auth()->user()->id) {
            abort(403);
        }

        $promoCode->delete();

        return redirect()->route('leader.promo-codes.index')
            ->with('message', 'Промокод успешно удален');
    }
}
