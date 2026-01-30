<?php

namespace App\Http\Requests\API\Statement;

use Illuminate\Foundation\Http\FormRequest;

class IndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'merchant_id' => ['nullable', 'string', 'exists:merchants,uuid'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('merchant_id') && is_string($this->merchant_id)) {
            $merchantId = trim($this->merchant_id);
            $this->merge(['merchant_id' => $merchantId !== '' ? $merchantId : null]);
        }
    }
}
