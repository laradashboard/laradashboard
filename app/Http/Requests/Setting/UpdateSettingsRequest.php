<?php

declare(strict_types=1);

namespace App\Http\Requests\Setting;

use App\Enums\Hooks\SettingFilterHook;
use App\Models\Setting;
use App\Support\Facades\Hook;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('manage', Setting::class) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return Hook::applyFilters(SettingFilterHook::SETTINGS_UPDATE_VALIDATION_RULES, [
            /** @example {"app_name": "Lara Dashboard", "theme_primary_color": "#635bff", "theme_secondary_color": "#1f2937", "sidebar_bg_lite": "#FFFFFF", "sidebar_bg_dark": "#171f2e", "default_pagination": "10", "site_logo_lite": "/images/logo/lara-dashboard.png", "site_logo_dark": "/images/logo/lara-dashboard-dark.png", "global_custom_css": "", "global_custom_js": ""} */
            'settings' => 'required|array',
        ]);
    }
}
