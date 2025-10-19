<?php

namespace App\Http\Controllers\TeamLeader;

use App\Http\Controllers\Controller;
use App\Http\Resources\PromoCodeResource;
use App\Models\PromoCode;
use App\Models\User;
use Illuminate\Http\Request;
use App\DTO\PromoCode\PromoCodeCreateDTO;
use Illuminate\Support\Str;
use Inertia\Inertia;

class PromoCodeController extends Controller
{
    public function index()
    {
        // Получаем фильтры из запроса
        $filters = $this->getTableFilters();

        // Строим запрос на получение промокодов
        $query = PromoCode::query()
            ->where('team_leader_id', auth()->user()->id)
            ->latest();

        // Применяем фильтры
        if (!empty($filters->search)) {
            $query->where('code', 'like', '%' . $filters->search . '%');
        }

        if (!empty($filters->user)) {
            $query->whereHas('teamLeader', function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters->user . '%')
                    ->orWhere('email', 'like', '%' . $filters->user . '%');
            });
        }

        if ($filters->active) {
            $query->where('is_active', $filters->active);
        }

        // Пагинация результатов
        $promoCodes = $query->paginate();

        // Преобразуем результаты в ресурсы
        $promoCodes = PromoCodeResource::collection($promoCodes);

        // Используем ту же страницу, что и для тимлидера
        return Inertia::render('PromoCode/Index', [
            'promoCodes' => $promoCodes,
            'filters' => $filters,
            'isAdmin' => true
        ]);
    }

    public function create()
    {
        return Inertia::render('PromoCode/Add');
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'nullable|string|max:20|unique:promo_codes,code',
            'max_uses' => 'required|integer|min:0',
            'is_active' => 'required|boolean',
        ]);

        services()->promoCode()->create(new PromoCodeCreateDTO(
            team_leader_id: auth()->id(),
            code: (string) $request->input('code', ''),
            max_uses: (int) $request->input('max_uses'),
            is_active: (bool) $request->boolean('is_active'),
        ));

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
            'promoCode' => (new PromoCodeResource($promoCode))->resolve(),
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

        // Проверка лимита использований
        $is_active = $request->input('is_active');
        if ($is_active && $promoCode->max_uses > 0 && $promoCode->used_count >= $promoCode->max_uses) {
            return redirect()->back()->withErrors([
                'is_active' => 'Невозможно активировать промокод, так как достигнут лимит использований'
            ]);
        }

        $promoCode->update([
            'max_uses' => $request->input('max_uses'),
            'is_active' => $is_active,
        ]);

        return redirect()->back()
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
