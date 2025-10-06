{!! Hook::applyFilters(SettingFilterHook::SETTINGS_GENERAL_TAB_BEFORE_SECTION_START, '') !!}

<x-card>
    <x-slot name="header">
        {{ __('General Settings') }}
    </x-slot>
    <div class="space-y-6">
        <div class="flex">
            <div class="md:basis-1/2 relative">
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    {{ __('Site Name') }}
                </label>
                <input type="text" name="app_name" placeholder="{{ __('Enter site name') }}"
                    value="{{ config('settings.app_name') ?? '' }}" @if (config('app.demo_mode', false)) disabled @endif
                    class="form-control" data-tooltip-target="tooltip-app-name">
                <div id="tooltip-app-name" role="tooltip"
                    class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-md shadow-xs opacity-0 tooltip dark:bg-gray-700">
                    {{ __('Editing site name is disabled in demo mode.') }}
                    <div class="tooltip-arrow" data-popper-arrow></div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <!-- Column 1: Site Logo Full Lite and Dark -->
            <div>
                <div class="mt-2">
                    <x-inputs.file-input
                        name="site_logo_lite"
                        id="site_logo_lite"
                        label="{{ __('Site Logo Full (Lite Version)') }}"
                        :existingAttachment="config('settings.site_logo_lite') !== '' && !empty(config('settings.site_logo_lite')) ? config('settings.site_logo_lite') : null"
                        :existingAltText="''"
                    />
                </div>

                <div class="mt-2">
                    <x-inputs.file-input
                        name="site_logo_dark"
                        id="site_logo_dark"
                        label="{{ __('Site Logo Full (Dark Version)') }}"
                        :existingAttachment="config('settings.site_logo_dark') !== '' && !empty(config('settings.site_logo_dark')) ? config('settings.site_logo_dark') : null"
                        :existingAltText="''"
                    />
                </div>
            </div>

            <!-- Column 2: Site Icon and Favicon -->
            <div>
                <div class="mt-2">
                    <x-inputs.file-input
                        name="site_icon"
                        id="site_icon"
                        label="{{ __('Site Icon') }}"
                        :existingAttachment="config('settings.site_icon') !== '' && !empty(config('settings.site_icon')) ? config('settings.site_icon') : null"
                        :existingAltText="''"
                    />
                </div>

                <div class="mt-2">
                    <x-inputs.file-input
                        name="site_favicon"
                        id="site_favicon"
                        label="{{ __('Site Favicon') }}"
                        :existingAttachment="config('settings.site_favicon') !== '' && !empty(config('settings.site_favicon')) ? config('settings.site_favicon') : null"
                        :existingAltText="''"
                    />
                </div>
            </div>
        </div>
    </div>
    {!! Hook::applyFilters(SettingFilterHook::SETTINGS_GENERAL_TAB_BEFORE_SECTION_END, '') !!}
</x-card>

