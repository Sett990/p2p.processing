<?php

namespace App\Http\Controllers\TeamLeader;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\PromoCode;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReferralController extends Controller
{
    /**
     * Отображает список рефералов команды лидера.
     */
    public function index()
    {
        // Получаем все промокоды, созданные текущим пользователем
        $promoCodes = PromoCode::where('team_leader_id', auth()->id())->pluck('id');
        
        // Получаем пользователей, которые использовали эти промокоды
        $referrals = User::with(['promoCode'])
            ->whereIn('promo_code_id', $promoCodes)
            ->latest('promo_used_at')
            ->paginate(10);
            
        return Inertia::render('Referral/Index', [
            'referrals' => UserResource::collection($referrals)
        ]);
    }
}
