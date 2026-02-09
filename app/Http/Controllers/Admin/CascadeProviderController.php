<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ProviderType;
use App\Http\Controllers\Controller;
use App\Models\CascadeProvider;
use App\Services\Money\Currency;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class CascadeProviderController extends Controller
{
    public function index()
    {
        $providers = CascadeProvider::query()
            ->where(function ($query) {
                $query->whereNull('provider_type')
                    ->orWhere('provider_type', '!=', ProviderType::INTERNAL->value);
            })
            ->where('code', '!=', 'internal')
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
                    'base_url' => $provider->base_url,
                    'access_token' => $provider->access_token,
                    'merchant_id' => $provider->merchant_id,
                    'callback_url' => $provider->callback_url,
                    'currency_code' => $provider->currency_code,
                    'timeout' => $provider->timeout,
                    'verify_ssl' => (bool) $provider->verify_ssl,
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
            'currencyCodes' => Currency::getAllCodes(),
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
            'base_url' => ['nullable', 'string', 'max:255'],
            'access_token' => ['nullable', 'string', 'max:255'],
            'merchant_id' => ['nullable', 'string', 'max:255'],
            'callback_url' => ['nullable', 'string', 'max:255', 'url'],
            'currency_code' => ['nullable', 'string', Rule::in(Currency::getAllCodes())],
            'timeout' => ['nullable', 'integer', 'min:1'],
            'verify_ssl' => ['nullable', 'boolean'],
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

        $this->validateProviderSettings($data['code'], $data);

        CascadeProvider::create([
            'code' => $data['code'],
            'name' => $data['name'],
            'provider_type' => ProviderType::EXTERNAL->value,
            'is_active' => $data['is_active'],
            'weight' => $data['weight'],
            'priority' => $data['priority'],
            'description' => $data['description'],
            'base_url' => $data['base_url'] ?? null,
            'access_token' => $data['access_token'] ?? null,
            'merchant_id' => $data['merchant_id'] ?? null,
            'callback_url' => $data['callback_url'] ?? null,
            'currency_code' => $data['currency_code'] ?? null,
            'timeout' => $data['timeout'] ?? null,
            'verify_ssl' => isset($data['verify_ssl']) ? (bool) $data['verify_ssl'] : true,
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
            'base_url' => ['nullable', 'string', 'max:255'],
            'access_token' => ['nullable', 'string', 'max:255'],
            'merchant_id' => ['nullable', 'string', 'max:255'],
            'callback_url' => ['nullable', 'string', 'max:255', 'url'],
            'currency_code' => ['nullable', 'string', Rule::in(Currency::getAllCodes())],
            'timeout' => ['nullable', 'integer', 'min:1'],
            'verify_ssl' => ['nullable', 'boolean'],
        ]);

        $this->validateProviderSettings($cascadeProvider->code, $data);

        $cascadeProvider->update([
            'name' => $data['name'],
            'is_active' => $data['is_active'],
            'weight' => $data['weight'],
            'priority' => $data['priority'],
            'description' => $data['description'],
            'base_url' => $data['base_url'] ?? null,
            'access_token' => $data['access_token'] ?? null,
            'merchant_id' => $data['merchant_id'] ?? null,
            'callback_url' => $data['callback_url'] ?? null,
            'currency_code' => $data['currency_code'] ?? null,
            'timeout' => $data['timeout'] ?? null,
            'verify_ssl' => isset($data['verify_ssl']) ? (bool) $data['verify_ssl'] : true,
        ]);

        return response()->json(['success' => true]);
    }

    private function validateProviderSettings(string $code, array $data): void
    {
        if ($code !== 'p2pprocessing') {
            return;
        }

        $requiredKeys = ['base_url', 'access_token', 'merchant_id', 'currency_code'];
        $missing = collect($requiredKeys)
            ->filter(fn (string $key) => empty($data[$key]))
            ->values()
            ->all();

        if ($missing !== []) {
            $errors = [];
            foreach ($missing as $key) {
                $errors[$key] = ['Обязательное поле.'];
            }
            throw ValidationException::withMessages($errors);
        }
    }
}
