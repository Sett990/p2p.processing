<?php

namespace App\Http\Controllers\Admin;

use App\Enums\MarketEnum;
use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\MerchantResource;
use App\Models\Category;
use App\Models\Merchant;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class MerchantController extends Controller
{
    public function index()
    {
        $merchants = Merchant::query()
            ->with('user')
            ->orderByDesc('id')
            ->paginate(10);

        $merchants = MerchantResource::collection($merchants);

        return Inertia::render('Merchant/Index', compact('merchants'));
    }

    public function show(Merchant $merchant)
    {
        $merchant = new MerchantResource($merchant->load('categories'));
        $categories = CategoryResource::collection(Category::orderBy('name')->get())->resolve();
        $markets = MarketEnum::cases();
dd($categories);
        return Inertia::render('Merchant/Show', compact('merchant', 'categories', 'markets'));
    }

    public function ban(Merchant $merchant)
    {
        $merchant->update([
            'banned_at' => now(),
            'validated_at' => now(),
        ]);
    }

    public function unban(Merchant $merchant)
    {
        $merchant->update([
            'banned_at' => null,
            'validated_at' => now(),
        ]);
    }

    public function validated(Merchant $merchant)
    {
        $merchant->update([
            'validated_at' => now(),
        ]);
    }

    public function updateSettings(Request $request, Merchant $merchant)
    {
        $request->validate([
            'market' => ['required', Rule::enum(MarketEnum::class)],
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
        ]);

        $merchant->update([
            'market' => $request->market
        ]);

        if ($request->has('categories')) {
            $merchant->categories()->sync($request->categories);
        }
    }
}
