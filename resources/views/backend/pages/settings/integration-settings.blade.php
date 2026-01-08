{!! Hook::applyFilters(SettingFilterHook::SETTINGS_INTEGRATIONS_TAB_BEFORE_SECTION_START, '') !!}
@include('backend.pages.settings.ai-settings')
{!! Hook::applyFilters(SettingFilterHook::SETTINGS_INTEGRATIONS_TAB_BEFORE_SECTION_END, '') !!}

{!! Hook::applyFilters(SettingFilterHook::SETTINGS_INTEGRATIONS_TAB_AFTER_SECTION_END, '') !!}
<div class="mt-6">
    <x-card>
        <x-slot name="header">
            {{ __('Google Analytics') }}
        </x-slot>

        <div class="space-y-2">
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                {{ __('Google Analytics') }}
            </label>
            <textarea name="google_analytics_script" rows="6" placeholder="{{ __('Paste your Google Analytics script here') }}"
                @if (config('app.demo_mode', false)) disabled @endif
                class="form-control h-20"
                data-tooltip-target="tooltip-google-analytics">{{ config('settings.google_analytics_script') ?? '' }}</textarea>

            @if (config('app.demo_mode', false))
            <div id="tooltip-google-analytics" role="tooltip" class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-md shadow-xs opacity-0 tooltip dark:bg-gray-700">
                {{ __('Editing this script is disabled in demo mode.') }}
                <div class="tooltip-arrow" data-popper-arrow></div>
            </div>
            @endif

            <p class="mt-2 text-sm text-gray-500 dark:text-gray-300">
                {{ __('Learn more about Google Analytics and how to set it up:') }}
                <a href="https://analytics.google.com/" target="_blank" class="text-primary hover:underline">
                    {{ __('Google Analytics') }}
                </a>
            </p>
        </div>
    </x-card>
</div>