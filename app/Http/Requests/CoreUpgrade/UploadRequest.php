<?php

declare(strict_types=1);

namespace App\Http\Requests\CoreUpgrade;

use App\Models\Setting;
use Illuminate\Foundation\Http\FormRequest;

class UploadRequest extends FormRequest
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
            'upgrade_file' => ['required', 'file', 'mimes:zip', 'max:102400'], // Max 100MB
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
            'upgrade_file.required' => __('Please select a zip file to upload.'),
            'upgrade_file.file' => __('The uploaded file is invalid.'),
            'upgrade_file.mimes' => __('Only .zip files are accepted.'),
            'upgrade_file.max' => __('The file size must not exceed 100MB.'),
        ];
    }
}
