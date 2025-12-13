<?php

declare(strict_types=1);

namespace App\Http\Requests\EmailConnection;

use App\Models\Setting;
use App\Services\EmailProviderRegistry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmailConnectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->can('manage', Setting::class);
    }

    public function rules(): array
    {
        $providerType = $this->input('provider_type');
        $provider = $providerType ? EmailProviderRegistry::getProvider($providerType) : null;
        $providerRules = $provider ? $provider->getValidationRules() : [];

        return array_merge([
            'name' => ['required', 'string', 'max:255'],
            'from_email' => ['required', 'email', 'max:255'],
            'from_name' => ['nullable', 'string', 'max:255'],
            'force_from_email' => ['boolean'],
            'force_from_name' => ['boolean'],
            'provider_type' => ['required', 'string', Rule::in(EmailProviderRegistry::all())],
            'settings' => ['nullable', 'array'],
            'credentials' => ['nullable', 'array'],
            'is_active' => ['boolean'],
            'is_default' => ['boolean'],
            'priority' => ['nullable', 'integer', 'min:1', 'max:1000'],
        ], $providerRules);
    }

    public function messages(): array
    {
        return [
            'name.required' => __('Connection name is required.'),
            'from_email.required' => __('From email address is required.'),
            'from_email.email' => __('Please enter a valid email address.'),
            'provider_type.required' => __('Please select a provider type.'),
            'provider_type.in' => __('Invalid provider type selected.'),
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active', true),
            'is_default' => $this->boolean('is_default', false),
            'force_from_email' => $this->boolean('force_from_email', false),
            'force_from_name' => $this->boolean('force_from_name', false),
        ]);
    }
}
