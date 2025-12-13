{!! Hook::applyFilters(SettingFilterHook::SETTINGS_APPEARANCE_TAB_BEFORE_SECTION_START, '') !!}
<x-card>
    <x-slot name="header">
        {{ __('Site Appearance') }}
    </x-slot>
    <div class="space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div>
                <label for="color-picker-theme_primary_color" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    {{ __('Theme Primary Color') }}
                </label>
                <div class="flex gap-2 items-center">
                    <div>
                        <input type="color" id="color-picker-theme_primary_color" name="theme_primary_color"
                            value="{{ config('settings.theme_primary_color') ?? '' }}"
                            class="h-11 w-11 cursor-pointer dark:border-gray-700"
                            data-tooltip-target="tooltip-theme_primary_color" onchange="syncColor('theme_primary_color')">
                        <div id="tooltip-theme_primary_color" role="tooltip"
                            class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-md shadow-xs opacity-0 tooltip dark:bg-gray-700">
                            {{ __('Choose color') }}
                            <div class="tooltip-arrow" data-popper-arrow></div>
                        </div>
                    </div>
                    <input type="text" id="input-theme_primary_color" name="theme_primary_color_text"
                        value="{{ config('settings.theme_primary_color') ?? '#ffffff' }}"
                        class="form-control"
                        placeholder="#ffffff" oninput="syncColor('theme_primary_color', true)">
                </div>
            </div>

            <!-- Theme Secondary Color -->
            <div>
                <label for="color-picker-theme_secondary_color" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    {{ __('Theme Secondary Color') }}
                </label>
                <div class="flex gap-2 items-center">
                    <div>
                        <input type="color" id="color-picker-theme_secondary_color" name="theme_secondary_color"
                            value="{{ config('settings.theme_secondary_color') ?? '' }}"
                            class="h-11 w-11 cursor-pointer dark:border-gray-700"
                            data-tooltip-target="tooltip-theme_secondary_color" onchange="syncColor('theme_secondary_color')">
                        <div id="tooltip-theme_secondary_color" role="tooltip"
                            class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-md shadow-xs opacity-0 tooltip dark:bg-gray-700">
                            {{ __('Choose color') }}
                            <div class="tooltip-arrow" data-popper-arrow></div>
                        </div>
                    </div>
                    <input type="text" id="input-theme_secondary_color" name="theme_secondary_color_text"
                        value="{{ config('settings.theme_secondary_color') ?? '#ffffff' }}"
                        class="form-control"
                        placeholder="#ffffff" oninput="syncColor('theme_secondary_color', true)">
                </div>
            </div>
        </div>

        <div class="flex">
            <div class="md:basis-1/2">
                <label for="default_mode" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    {{ __('Default Mode') }}
                </label>
                <select id="default_mode" name="default_mode"
                    class="form-control">
                    <option value="lite" {{ config('settings.default_mode') == 'lite' ? 'selected' : '' }}>{{ __('Lite') }}
                    </option>
                    <option value="dark"{{ config('settings.default_mode') == 'dark' ? 'selected' : '' }}>{{ __('Dark') }}
                    </option>
                    <option value="system"{{ config('settings.default_mode') == 'system' ? 'selected' : '' }}>{{ __('System') }}
                    </option>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <!-- Lite Mode Colors -->
            <div>
                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-4">{{ __('Lite Mode Colors') }}</h4>

                <!-- Navbar Background Color (Lite Mode) -->
                <div class="mb-4">
                    <label for="color-picker-navbar_bg_lite" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        {{ __('Navbar Background Color') }}
                    </label>
                    <div class="flex gap-2 items-center">
                        <div>
                            <input type="color" id="color-picker-navbar_bg_lite" name="navbar_bg_lite"
                                value="{{ config('settings.navbar_bg_lite') ?? '' }}"
                                class="h-11 w-11 cursor-pointer dark:border-gray-700"
                                data-tooltip-target="tooltip-navbar_bg_lite" onchange="syncColor('navbar_bg_lite')">
                            <div id="tooltip-navbar_bg_lite" role="tooltip"
                                class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-md shadow-xs opacity-0 tooltip dark:bg-gray-700">
                                {{ __('Choose color') }}
                                <div class="tooltip-arrow" data-popper-arrow></div>
                            </div>
                        </div>
                        <input type="text" id="input-navbar_bg_lite" name="navbar_bg_lite_text"
                            value="{{ config('settings.navbar_bg_lite') ?? '#ffffff' }}"
                            class="form-control"
                            placeholder="#ffffff" oninput="syncColor('navbar_bg_lite', true)">
                    </div>
                </div>

                <!-- Sidebar Background Color (Lite Mode) -->
                <div class="mb-4">
                    <label for="color-picker-sidebar_bg_lite" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        {{ __('Sidebar Background Color') }}
                    </label>
                    <div class="flex gap-2 items-center">
                        <div>
                            <input type="color" id="color-picker-sidebar_bg_lite" name="sidebar_bg_lite"
                                value="{{ config('settings.sidebar_bg_lite') ?? '' }}"
                                class="h-11 w-11 cursor-pointer dark:border-gray-700"
                                data-tooltip-target="tooltip-sidebar_bg_lite" onchange="syncColor('sidebar_bg_lite')">
                            <div id="tooltip-sidebar_bg_lite" role="tooltip"
                                class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-md shadow-xs opacity-0 tooltip dark:bg-gray-700">
                                {{ __('Choose color') }}
                                <div class="tooltip-arrow" data-popper-arrow></div>
                            </div>
                        </div>
                        <input type="text" id="input-sidebar_bg_lite" name="sidebar_bg_lite_text"
                            value="{{ config('settings.sidebar_bg_lite') ?? '#ffffff' }}"
                            class="form-control"
                            placeholder="#ffffff" oninput="syncColor('sidebar_bg_lite', true)">
                    </div>
                </div>

                <!-- Navbar Text Color (Lite Mode) -->
                <div class="mb-4">
                    <label for="color-picker-navbar_text_lite" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        {{ __('Navbar Text Color') }}
                    </label>
                    <div class="flex gap-2 items-center">
                        <div>
                            <input type="color" id="color-picker-navbar_text_lite" name="navbar_text_lite"
                                value="{{ config('settings.navbar_text_lite') ?? '' }}"
                                class="h-11 w-11 cursor-pointer dark:border-gray-700"
                                data-tooltip-target="tooltip-navbar_text_lite" onchange="syncColor('navbar_text_lite')">
                            <div id="tooltip-navbar_text_lite" role="tooltip"
                                class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-md shadow-xs opacity-0 tooltip dark:bg-gray-700">
                                {{ __('Choose color') }}
                                <div class="tooltip-arrow" data-popper-arrow></div>
                            </div>
                        </div>
                        <input type="text" id="input-navbar_text_lite" name="navbar_text_lite_text"
                            value="{{ config('settings.navbar_text_lite') ?? '#ffffff' }}"
                            class="form-control"
                            placeholder="#ffffff" oninput="syncColor('navbar_text_lite', true)">
                    </div>
                </div>

                <!-- Sidebar Text Color (Lite Mode) -->
                <div class="mb-4">
                    <label for="color-picker-sidebar_text_lite" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        {{ __('Sidebar Text Color') }}
                    </label>
                    <div class="flex gap-2 items-center">
                        <div>
                            <input type="color" id="color-picker-sidebar_text_lite" name="sidebar_text_lite"
                                value="{{ config('settings.sidebar_text_lite') ?? '' }}"
                                class="h-11 w-11 cursor-pointer dark:border-gray-700"
                                data-tooltip-target="tooltip-sidebar_text_lite" onchange="syncColor('sidebar_text_lite')">
                            <div id="tooltip-sidebar_text_lite" role="tooltip"
                                class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-md shadow-xs opacity-0 tooltip dark:bg-gray-700">
                                {{ __('Choose color') }}
                                <div class="tooltip-arrow" data-popper-arrow></div>
                            </div>
                        </div>
                        <input type="text" id="input-sidebar_text_lite" name="sidebar_text_lite_text"
                            value="{{ config('settings.sidebar_text_lite') ?? '#ffffff' }}"
                            class="form-control"
                            placeholder="#ffffff" oninput="syncColor('sidebar_text_lite', true)">
                    </div>
                </div>
            </div>

            <!-- Dark Mode Colors -->
            <div>
                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-4">{{ __('Dark Mode Colors') }}</h4>

                <!-- Navbar Background Color (Dark Mode) -->
                <div class="mb-4">
                    <label for="color-picker-navbar_bg_dark" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        {{ __('Navbar Background Color') }}
                    </label>
                    <div class="flex gap-2 items-center">
                        <div>
                            <input type="color" id="color-picker-navbar_bg_dark" name="navbar_bg_dark"
                                value="{{ config('settings.navbar_bg_dark') ?? '' }}"
                                class="h-11 w-11 cursor-pointer dark:border-gray-700"
                                data-tooltip-target="tooltip-navbar_bg_dark" onchange="syncColor('navbar_bg_dark')">
                            <div id="tooltip-navbar_bg_dark" role="tooltip"
                                class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-md shadow-xs opacity-0 tooltip dark:bg-gray-700">
                                {{ __('Choose color') }}
                                <div class="tooltip-arrow" data-popper-arrow></div>
                            </div>
                        </div>
                        <input type="text" id="input-navbar_bg_dark" name="navbar_bg_dark_text"
                            value="{{ config('settings.navbar_bg_dark') ?? '#ffffff' }}"
                            class="form-control"
                            placeholder="#ffffff" oninput="syncColor('navbar_bg_dark', true)">
                    </div>
                </div>

                <!-- Sidebar Background Color (Dark Mode) -->
                <div class="mb-4">
                    <label for="color-picker-sidebar_bg_dark" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        {{ __('Sidebar Background Color') }}
                    </label>
                    <div class="flex gap-2 items-center">
                        <div>
                            <input type="color" id="color-picker-sidebar_bg_dark" name="sidebar_bg_dark"
                                value="{{ config('settings.sidebar_bg_dark') ?? '' }}"
                                class="h-11 w-11 cursor-pointer dark:border-gray-700"
                                data-tooltip-target="tooltip-sidebar_bg_dark" onchange="syncColor('sidebar_bg_dark')">
                            <div id="tooltip-sidebar_bg_dark" role="tooltip"
                                class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-md shadow-xs opacity-0 tooltip dark:bg-gray-700">
                                {{ __('Choose color') }}
                                <div class="tooltip-arrow" data-popper-arrow></div>
                            </div>
                        </div>
                        <input type="text" id="input-sidebar_bg_dark" name="sidebar_bg_dark_text"
                            value="{{ config('settings.sidebar_bg_dark') ?? '#ffffff' }}"
                            class="form-control"
                            placeholder="#ffffff" oninput="syncColor('sidebar_bg_dark', true)">
                    </div>
                </div>

                <!-- Navbar Text Color (Dark Mode) -->
                <div class="mb-4">
                    <label for="color-picker-navbar_text_dark" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        {{ __('Navbar Text Color') }}
                    </label>
                    <div class="flex gap-2 items-center">
                        <div>
                            <input type="color" id="color-picker-navbar_text_dark" name="navbar_text_dark"
                                value="{{ config('settings.navbar_text_dark') ?? '' }}"
                                class="h-11 w-11 cursor-pointer dark:border-gray-700"
                                data-tooltip-target="tooltip-navbar_text_dark" onchange="syncColor('navbar_text_dark')">
                            <div id="tooltip-navbar_text_dark" role="tooltip"
                                class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-md shadow-xs opacity-0 tooltip dark:bg-gray-700">
                                {{ __('Choose color') }}
                                <div class="tooltip-arrow" data-popper-arrow></div>
                            </div>
                        </div>
                        <input type="text" id="input-navbar_text_dark" name="navbar_text_dark_text"
                            value="{{ config('settings.navbar_text_dark') ?? '#ffffff' }}"
                            class="form-control"
                            placeholder="#ffffff" oninput="syncColor('navbar_text_dark', true)">
                    </div>
                </div>

                <!-- Sidebar Text Color (Dark Mode) -->
                <div class="mb-4">
                    <label for="color-picker-sidebar_text_dark" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        {{ __('Sidebar Text Color') }}
                    </label>
                    <div class="flex gap-2 items-center">
                        <div>
                            <input type="color" id="color-picker-sidebar_text_dark" name="sidebar_text_dark"
                                value="{{ config('settings.sidebar_text_dark') ?? '' }}"
                                class="h-11 w-11 cursor-pointer dark:border-gray-700"
                                data-tooltip-target="tooltip-sidebar_text_dark" onchange="syncColor('sidebar_text_dark')">
                            <div id="tooltip-sidebar_text_dark" role="tooltip"
                                class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-md shadow-xs opacity-0 tooltip dark:bg-gray-700">
                                {{ __('Choose color') }}
                                <div class="tooltip-arrow" data-popper-arrow></div>
                            </div>
                        </div>
                        <input type="text" id="input-sidebar_text_dark" name="sidebar_text_dark_text"
                            value="{{ config('settings.sidebar_text_dark') ?? '#ffffff' }}"
                            class="form-control"
                            placeholder="#ffffff" oninput="syncColor('sidebar_text_dark', true)">
                    </div>
                </div>
            </div>
        </div>
    </div>
    {!! Hook::applyFilters(SettingFilterHook::SETTINGS_APPEARANCE_TAB_BEFORE_SECTION_END, '') !!}
</x-card>
{!! Hook::applyFilters(SettingFilterHook::SETTINGS_APPEARANCE_TAB_AFTER_SECTION_END, '') !!}

<!-- Custom CSS & JS Section -->
<x-card class="mt-6">
    <x-slot name="header">
        {{ __('Custom CSS & JavaScript') }}
    </x-slot>
    <div class="space-y-4">
        <div>
            <label for="global_custom_css" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                {{ __('Global Custom CSS') }}
            </label>
            <textarea id="global_custom_css" name="global_custom_css" rows="6"
                class="form-control h-16"
                placeholder=".my-class { color: red; }">{{ config('settings.global_custom_css') }}</textarea>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-300">
                {{ __('Add custom CSS that will be applied to all pages') }}
            </p>
        </div>

        <div>
            <label for="global_custom_js" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                {{ __('Global Custom JavaScript') }}
            </label>
            <textarea id="global_custom_js" name="global_custom_js" rows="6"
                class="form-control h-16"
                placeholder="document.addEventListener('DOMContentLoaded', function() { /* Your code */ });">{{ config('settings.global_custom_js') }}</textarea>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-300">
                {{ __('Add custom JavaScript that will be loaded on all pages') }}
            </p>
        </div>
    </div>
</x-card>

@push('scripts')
<script>
    function syncColor(field, fromInput = false) {
        const colorPicker = document.getElementById(`color-picker-${field}`);
        const textInput = document.getElementById(`input-${field}`);
        if (fromInput) {
            colorPicker.value = textInput.value || '';
        } else {
            textInput.value = colorPicker.value || '';
        }
    }
</script>
@endpush
