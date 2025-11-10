<?php

namespace App\Http\Controllers\Admin;

use App\Enums\MarketEnum;
use App\Http\Controllers\Controller;
use App\Http\Resources\MerchantResource;
use App\Models\Merchant;
use Illuminate\Http\JsonResponse;
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
            ->paginate(request()->per_page ?? 10);

        $merchants = MerchantResource::collection($merchants);

        return Inertia::render('Merchant/Index', compact('merchants'));
    }

    public function indexData(Request $request): JsonResponse
    {
        $merchants = Merchant::query()
            ->with('user')
            ->orderByDesc('id')
            ->paginate($request->get('per_page', 10));

        return response()->json(
            MerchantResource::collection($merchants)->response()->getData(true)
        );
    }

    public function ban(Request $request, Merchant $merchant)
    {
        $merchant->update([
            'banned_at' => now(),
            'validated_at' => now(),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'merchant' => MerchantResource::make($merchant->fresh('categories'))->resolve(),
            ]);
        }

        return back();
    }

    public function unban(Request $request, Merchant $merchant)
    {
        $merchant->update([
            'banned_at' => null,
            'validated_at' => now(),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'merchant' => MerchantResource::make($merchant->fresh('categories'))->resolve(),
            ]);
        }

        return back();
    }

    public function validated(Request $request, Merchant $merchant)
    {
        $merchant->update([
            'validated_at' => now(),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'merchant' => MerchantResource::make($merchant->fresh('categories'))->resolve(),
            ]);
        }

        return back();
    }

    public function updateSettings(Request $request, Merchant $merchant)
    {
        $request->validate([
            'market' => ['required', Rule::enum(MarketEnum::class)],
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
            'max_order_wait_time' => 'nullable|integer|min:1000',
            'min_order_amounts' => 'nullable|array',
            'min_order_amounts.*' => 'numeric|min:0',
        ]);

        $merchant->update([
            'market' => $request->market,
            'max_order_wait_time' => $request->max_order_wait_time,
            'min_order_amounts' => $request->min_order_amounts,
        ]);

        if ($request->has('categories')) {
            $merchant->categories()->sync($request->categories);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'merchant' => MerchantResource::make($merchant->fresh()->load('categories'))->resolve(),
            ]);
        }

        return back()->with([
            'merchant' => new MerchantResource($merchant->fresh()->load('categories')),
        ]);
    }
}
