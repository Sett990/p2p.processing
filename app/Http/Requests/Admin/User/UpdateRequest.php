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
            // Используем поле login, но проверяем уникальность по колонке email
            'login' => ['required', 'string', 'max:255', Rule::unique(User::class, 'email')->ignore($user->id)],
            'role_id' => ['required', 'integer', 'exists:roles,id'],
            'banned' => ['required', 'boolean'],
            'payouts_enabled' => ['required', 'boolean'],
            'stop_traffic' => ['required', 'boolean'],
            'is_vip' => ['required', 'boolean'],
            'referral_commission_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'reserve_balance_limit' => ['nullable', 'integer', 'min:0'],
            'promo_code' => ['nullable', 'string', 'exists:promo_codes,code'],
        ];
    }

    public function attributes()
    {
        return [
            'role_id' => __('роль'),
            'payouts_enabled' => __('функционал выплат'),
            'stop_traffic' => __('остановка трафика'),
            'is_vip' => __('VIP статус'),
            'referral_commission_percentage' => __('процент комиссии от рефералов'),
            'reserve_balance_limit' => __('страховой депозит'),
            'promo_code' => __('промокод'),
        ];
    }
}
