@php echo ld_apply_filters('settings_integrations_tab_before_section_start', ''); @endphp
<div class="rounded-md border border-gray-200 dark:border-gray-800 dark:bg-white/[0.03]">
    <div class="px-5 py-4 sm:px-6 sm:py-5">
        <h3 class="text-base font-medium text-gray-700 dark:text-white/90">
            {{ __('Integration Settings') }}
        </h3>
    </div>
    <div class="space-y-6 border-t border-gray-100 p-5 sm:p-6 dark:border-gray-800">
        <div class="relative">
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                {{ __('Google Analytics') }}
            </label>
            <textarea name="google_analytics_script" rows="6" placeholder="{{ __('Paste your Google Analytics script here') }}"
                @if (config('app.demo_mode', false)) disabled @endif
                class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 w-full rounded-md border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-700 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                data-tooltip-target="tooltip-google-analytics">{{ config('settings.google_analytics_script') ?? '' }}</textarea>

            @if (config('app.demo_mode', false))
            <div id="tooltip-google-analytics" role="tooltip" class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-md shadow-xs opacity-0 tooltip dark:bg-gray-700">
                {{ __('Editing this script is disabled in demo mode.') }}
                <div class="tooltip-arrow" data-popper-arrow></div>
            </div>
            @endif

            <p class="mt-2 text-sm text-gray-500 dark:text-gray-300">
                {{ __('Learn more about Google Analytics and how to set it up:') }}
                <a href="https://analytics.google.com/" target="_blank" class="text-blue-500 hover:underline">
                    {{ __('Google Analytics') }}
                </a>
            </p>
        </div>
    </div>
    @php echo ld_apply_filters('settings_integrations_tab_before_section_end', '') @endphp
</div>
@php echo ld_apply_filters('settings_integrations_tab_after_section_end', '') @endphp
