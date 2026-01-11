<?php

declare(strict_types=1);

namespace App\Http\Requests\CoreUpgrade;

use App\Models\Setting;
use Illuminate\Foundation\Http\FormRequest;

class DeleteBackupRequest extends FormRequest
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
            'backup_file' => ['required', 'string', 'max:255'],
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
            'backup_file.required' => __('Backup file is required.'),
        ];
    }
}
