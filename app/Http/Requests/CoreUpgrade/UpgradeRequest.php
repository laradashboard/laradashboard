<?php

declare(strict_types=1);

namespace App\Http\Requests\CoreUpgrade;

use App\Models\Setting;
use Illuminate\Foundation\Http\FormRequest;

class UpgradeRequest extends FormRequest
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
            'version' => ['required', 'string', 'max:20'],
            'create_backup' => ['boolean'],
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
            'version.required' => __('Version is required.'),
            'version.max' => __('Version must not exceed 20 characters.'),
        ];
    }
}
