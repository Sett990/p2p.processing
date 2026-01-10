<?php

namespace App\Http\Requests\Admin\App;

use Illuminate\Foundation\Http\FormRequest;

class UploadApkRequest extends FormRequest
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
        return [
            'apk' => ['required', 'file', 'mimes:apk', 'mimetypes:application/vnd.android.package-archive', 'max:20480'],
        ];
    }

    public function attributes(): array
    {
        return [
            'apk' => 'файл приложения',
        ];
    }
}
