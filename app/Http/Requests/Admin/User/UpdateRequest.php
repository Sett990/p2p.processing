<?php

namespace App\Http\Requests\Admin\User;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $user = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
            'role_id' => ['required', 'integer', 'exists:roles,id'],
            'banned' => ['required', 'boolean'],
            'order_service_commission_rate' => ['nullable', 'numeric', 'min:0'],
            'payout_service_commission_rate' => ['nullable', 'numeric', 'min:0'],
            'payouts_enabled' => ['required', 'boolean'],
        ];
    }

    public function attributes()
    {
        return [
            'role_id' => __('роль'),
            'order_service_commission_rate' => __('комиссия сервиса'),
            'payout_service_commission_rate' => __('комиссия сервиса'),
            'payouts_enabled' => __('функционал выплат'),
        ];
    }
}
