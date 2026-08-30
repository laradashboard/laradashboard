<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

class RemoveLicenseRequest extends ManagesModuleLicenseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'module_slug' => ['required', 'string'],
        ];
    }
}
