<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

class StoreLicenseRequest extends ManagesModuleLicenseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'license_key' => ['required', 'string', 'min:10'],
            'module_slug' => ['required', 'string'],
            'module_name' => ['nullable', 'string'],
        ];
    }
}
