<?php

declare(strict_types=1);

namespace App\Http\Requests\Profile;

use App\Http\Requests\FormRequest;

class UpdateProfileAdditionalRequest extends FormRequest
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
            'display_name' => 'nullable|string|max:255',
            'bio' => 'nullable|string|max:1000',
            'timezone' => 'nullable|string|max:100',
            'locale' => 'nullable|string|max:10',
            'social_facebook' => 'nullable|string|url|max:255',
            'social_x' => 'nullable|string|url|max:255',
            'social_youtube' => 'nullable|string|url|max:255',
            'social_linkedin' => 'nullable|string|url|max:255',
            'social_website' => 'nullable|string|url|max:255',
        ];
    }
}
