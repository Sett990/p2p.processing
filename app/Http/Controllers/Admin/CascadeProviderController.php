<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ProviderType;
use App\Http\Controllers\Controller;
use App\Models\CascadeProvider;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class CascadeProviderController extends Controller
{
    public function index()
    {
        $providers = CascadeProvider::query()
            ->orderByDesc('is_active')
            ->orderBy('priority')
            ->orderBy('id')
            ->get()
            ->map(function (CascadeProvider $provider) {
                return [
                    'id' => $provider->id,
                    'code' => $provider->code,
                    'name' => $provider->name,
                    'provider_type' => $provider->provider_type?->value ?? $provider->provider_type,
                    'is_active' => (bool) $provider->is_active,
                    'weight' => $provider->weight,
                    'priority' => $provider->priority,
                    'description' => $provider->description,
                    'config_json' => $provider->config
                        ? json_encode($provider->config, JSON_PRETTY_PRINT)
                        : '',
                    'updated_at' => $provider->updated_at,
                ];
            })
            ->values()
            ->toArray();

        $availableIntegrationCodes = services()->cascadeProvider()->getAvailableIntegrationCodes();
        $existingCodes = collect($providers)->pluck('code')->all();
        $missingCodes = array_values(array_diff($availableIntegrationCodes, $existingCodes));

        return Inertia::render('Admin/Cascade/Providers/Index', [
            'providers' => $providers,
            'nextIntegrationCode' => $missingCodes[0] ?? null,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:64'],
            'name' => ['required', 'string', 'max:255'],
            'is_active' => ['required', 'boolean'],
            'weight' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'priority' => ['nullable', 'integer', 'min:0'],
            'description' => ['nullable', 'string', 'max:1000'],
            'config_json' => ['nullable', 'string'],
        ]);

        $availableCodes = services()->cascadeProvider()->getAvailableIntegrationCodes();
        if (! in_array($data['code'], $availableCodes, true)) {
            throw ValidationException::withMessages([
                'code' => ['Указан неизвестный код интеграции.'],
            ]);
        }

        if (CascadeProvider::where('code', $data['code'])->exists()) {
            throw ValidationException::withMessages([
                'code' => ['Интеграция с таким кодом уже существует.'],
            ]);
        }

        $config = $this->parseConfig($data['config_json'] ?? null);

        CascadeProvider::create([
            'code' => $data['code'],
            'name' => $data['name'],
            'provider_type' => ProviderType::EXTERNAL->value,
            'is_active' => $data['is_active'],
            'weight' => $data['weight'],
            'priority' => $data['priority'],
            'description' => $data['description'],
            'config' => $config,
        ]);

        return response()->json(['success' => true], 201);
    }

    public function update(Request $request, CascadeProvider $cascadeProvider)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'is_active' => ['required', 'boolean'],
            'weight' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'priority' => ['nullable', 'integer', 'min:0'],
            'description' => ['nullable', 'string', 'max:1000'],
            'config_json' => ['nullable', 'string'],
        ]);

        $config = $this->parseConfig($data['config_json'] ?? null);

        $cascadeProvider->update([
            'name' => $data['name'],
            'is_active' => $data['is_active'],
            'weight' => $data['weight'],
            'priority' => $data['priority'],
            'description' => $data['description'],
            'config' => $config,
        ]);

        return response()->json(['success' => true]);
    }

    private function parseConfig(?string $configJson): ?array
    {
        if ($configJson === null) {
            return null;
        }

        $configJson = trim($configJson);
        if ($configJson === '') {
            return null;
        }

        $decoded = json_decode($configJson, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw ValidationException::withMessages([
                'config_json' => ['Конфигурация должна быть валидным JSON.'],
            ]);
        }

        return $decoded;
    }
}
