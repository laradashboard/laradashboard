<?php

declare(strict_types=1);

namespace App\Http\Requests\CoreUpgrade;

use App\Models\Setting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateBackupRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('manageCoreUpgrades', Setting::class) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'backup_type' => ['required', 'string', Rule::in(['core', 'core_with_modules', 'core_with_uploads', 'full'])],
            'include_database' => ['boolean'],
            'include_vendor' => ['boolean'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'backup_type.required' => __('Please select a backup type.'),
            'backup_type.in' => __('Invalid backup type selected.'),
        ];
    }
}
