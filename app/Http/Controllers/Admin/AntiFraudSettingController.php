<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AntiFraudSetting\StoreRequest;
use App\Http\Requests\Admin\AntiFraudSetting\UpdateRequest;
use App\Models\AntiFraudSetting;
use App\Models\Merchant;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class AntiFraudSettingController extends Controller
{
    public function index()
    {
        $merchants = Merchant::query()
            ->select(['id', 'name', 'uuid'])
            ->orderBy('name')
            ->get();

        $settings = AntiFraudSetting::query()
            ->with('merchant:id,name,uuid')
            ->orderByDesc('id')
            ->get();

        return Inertia::render('Admin/AntiFraud/Index', compact('merchants', 'settings'));
    }

    public function store(StoreRequest $request): RedirectResponse
    {
        $data = $this->normalize($request->validated());
        AntiFraudSetting::query()->create($data);

        return back();
    }

    public function update(UpdateRequest $request, AntiFraudSetting $anti_fraud_setting): RedirectResponse
    {
        $data = $this->normalize($request->validated());
        $anti_fraud_setting->update($data);

        return back();
    }

    public function destroy(AntiFraudSetting $anti_fraud_setting): RedirectResponse
    {
        $anti_fraud_setting->delete();

        return back();
    }

    private function normalize(array $data): array
    {
        $data['enabled'] = (bool) ($data['enabled'] ?? false);
        $data['primary_rate_limits'] = $this->normalizeRateLimits($data['primary_rate_limits'] ?? []);
        $data['secondary_rate_limits'] = $this->normalizeRateLimits($data['secondary_rate_limits'] ?? []);

        return $data;
    }

    private function normalizeRateLimits(array $limits): array
    {
        return collect($limits)
            ->filter(fn (array $limit) => ! empty($limit['count']) && ! empty($limit['minutes']))
            ->map(fn (array $limit) => [
                'count' => (int) $limit['count'],
                'minutes' => (int) $limit['minutes'],
            ])
            ->values()
            ->toArray();
    }
}
