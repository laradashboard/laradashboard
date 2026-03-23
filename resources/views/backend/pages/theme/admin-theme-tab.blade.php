{!! Hook::applyFilters(SettingFilterHook::SETTINGS_APPEARANCE_TAB_BEFORE_SECTION_START, '') !!}

{{-- Color Palette Presets --}}
<x-card>
    <x-slot name="header">
        {{ __('Color Palette Presets') }}
    </x-slot>
    <x-slot name="headerDescription">
        {{ __('Choose a preset to auto-fill all theme colors, then customize further if needed.') }}
    </x-slot>

    @php
        $presetsJson = collect($colorPresets ?? [])->map(function ($preset) {
            return [
                'name' => $preset['name'],
                'colors' => $preset['colors'],
                'preview' => [
                    'primary' => $preset['colors']['primary'],
                    'secondary' => $preset['colors']['secondary'],
                    'bg' => $preset['colors']['sidebar_bg_lite'],
                    'dark' => $preset['colors']['sidebar_bg_dark'],
                ],
            ];
        })->values()->toJson();
    @endphp
    <div x-data="{
        presets: {{ $presetsJson }},
        selected: null,
        applyPreset(preset) {
            this.selected = preset.name;
            this.setColors({
                'theme_primary_color': preset.colors.primary,
                'theme_secondary_color': preset.colors.secondary,
                'navbar_bg_lite': preset.colors.navbar_bg_lite,
                'sidebar_bg_lite': preset.colors.sidebar_bg_lite,
                'navbar_text_lite': preset.colors.navbar_text_lite,
                'sidebar_text_lite': preset.colors.sidebar_text_lite,
                'navbar_bg_dark': preset.colors.navbar_bg_dark,
                'sidebar_bg_dark': preset.colors.sidebar_bg_dark,
                'navbar_text_dark': preset.colors.navbar_text_dark,
                'sidebar_text_dark': preset.colors.sidebar_text_dark,
            });
        },
        resetToDefault() {
            this.selected = 'default';
            this.setColors({
                'theme_primary_color': '#635bff',
                'theme_secondary_color': '#1f2937',
                'navbar_bg_lite': '#FFFFFF',
                'sidebar_bg_lite': '#FFFFFF',
                'navbar_text_lite': '#090909',
                'sidebar_text_lite': '#090909',
                'navbar_bg_dark': '#171f2e',
                'sidebar_bg_dark': '#171f2e',
                'navbar_text_dark': '#ffffff',
                'sidebar_text_dark': '#ffffff',
            });
        },
        setColors(map) {
            for (const [field, value] of Object.entries(map)) {
                const picker = document.getElementById('color-picker-' + field);
                const input = document.getElementById('input-' + field);
                if (picker) picker.value = value || '#ffffff';
                if (input) input.value = value;
            }
        }
    }">
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-5 gap-3">
            <template x-for="preset in presets" :key="preset.name">
                <button
                    type="button"
                    @click="applyPreset(preset)"
                    :class="selected === preset.name
                        ? 'ring-2 ring-primary ring-offset-2 dark:ring-offset-gray-800'
                        : 'ring-1 ring-gray-200 dark:ring-gray-700 hover:ring-gray-300 dark:hover:ring-gray-600'"
                    class="relative rounded-xl p-3 text-left transition-all duration-200 cursor-pointer bg-white dark:bg-gray-800 hover:shadow-md"
                >
                    {{-- Color Preview Swatches --}}
                    <div class="flex gap-1 mb-2.5">
                        <div class="h-8 flex-1 rounded-l-lg" :style="'background:' + preset.preview.primary"></div>
                        <div class="h-8 flex-1" :style="'background:' + preset.preview.secondary"></div>
                        <div class="h-8 flex-1" :style="'background:' + preset.preview.bg"></div>
                        <div class="h-8 flex-1 rounded-r-lg" :style="'background:' + preset.preview.dark"></div>
                    </div>

                    {{-- Mini Admin Preview --}}
                    <div class="rounded-md overflow-hidden border border-gray-200 dark:border-gray-600 mb-2" style="height: 48px;">
                        <div class="flex h-full">
                            {{-- Mini Sidebar --}}
                            <div class="w-1/4 flex flex-col gap-0.5 p-1" :style="'background:' + preset.colors.sidebar_bg_lite">
                                <div class="h-1 rounded-full w-3/4" :style="'background:' + preset.colors.sidebar_text_lite + '; opacity: 0.6'"></div>
                                <div class="h-1 rounded-full w-full" :style="'background:' + preset.colors.primary + '; opacity: 0.8'"></div>
                                <div class="h-1 rounded-full w-2/3" :style="'background:' + preset.colors.sidebar_text_lite + '; opacity: 0.4'"></div>
                            </div>
                            {{-- Mini Content --}}
                            <div class="flex-1 flex flex-col">
                                <div class="h-2.5 flex items-center px-1" :style="'background:' + preset.colors.navbar_bg_lite + '; border-bottom: 1px solid rgba(0,0,0,0.06)'">
                                    <div class="h-1 w-6 rounded-full" :style="'background:' + preset.colors.navbar_text_lite + '; opacity: 0.5'"></div>
                                </div>
                                <div class="flex-1 bg-gray-50 dark:bg-gray-100 p-1">
                                    <div class="h-1 w-2/3 rounded-full mb-0.5" style="background: rgba(0,0,0,0.1)"></div>
                                    <div class="h-3 rounded" :style="'background:' + preset.preview.primary + '; opacity: 0.15'"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Preset Name --}}
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-gray-700 dark:text-gray-300" x-text="preset.name"></span>
                        <iconify-icon
                            x-show="selected === preset.name"
                            icon="lucide:check-circle-2"
                            class="text-primary text-sm"
                            aria-hidden="true"
                        ></iconify-icon>
                    </div>
                </button>
            </template>

            {{-- Reset to Default --}}
            <button
                type="button"
                @click="resetToDefault()"
                :class="selected === 'default'
                    ? 'ring-2 ring-gray-400 ring-offset-2 dark:ring-offset-gray-800'
                    : 'ring-1 ring-dashed ring-gray-300 dark:ring-gray-600 hover:ring-gray-400 dark:hover:ring-gray-500'"
                class="relative rounded-xl p-3 text-left transition-all duration-200 cursor-pointer bg-gray-50 dark:bg-gray-800/50 hover:shadow-md"
            >
                {{-- Reset Icon --}}
                <div class="flex items-center justify-center h-8 mb-2.5">
                    <iconify-icon icon="lucide:rotate-ccw" class="text-gray-400 dark:text-gray-500 text-xl" aria-hidden="true"></iconify-icon>
                </div>

                {{-- Mini Preview - Default state --}}
                <div class="rounded-md overflow-hidden border border-gray-200 dark:border-gray-600 mb-2" style="height: 48px;">
                    <div class="flex h-full">
                        <div class="w-1/4 flex flex-col gap-0.5 p-1 bg-white dark:bg-gray-800">
                            <div class="h-1 rounded-full w-3/4 bg-gray-300"></div>
                            <div class="h-1 rounded-full w-full bg-gray-200"></div>
                            <div class="h-1 rounded-full w-2/3 bg-gray-200"></div>
                        </div>
                        <div class="flex-1 flex flex-col">
                            <div class="h-2.5 flex items-center px-1 bg-white dark:bg-gray-800" style="border-bottom: 1px solid rgba(0,0,0,0.06)">
                                <div class="h-1 w-6 rounded-full bg-gray-300"></div>
                            </div>
                            <div class="flex-1 bg-gray-50 dark:bg-gray-100 p-1">
                                <div class="h-1 w-2/3 rounded-full mb-0.5" style="background: rgba(0,0,0,0.1)"></div>
                                <div class="h-3 rounded" style="background: rgba(99,91,255,0.15)"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Reset Default') }}</span>
                    <iconify-icon
                        x-show="selected === 'default'"
                        icon="lucide:check-circle-2"
                        class="text-gray-500 text-sm"
                        aria-hidden="true"
                    ></iconify-icon>
                </div>
            </button>
        </div>
    </div>
</x-card>

<div class="mt-6"></div>

{{-- Admin Theme --}}
<x-card>
    <x-slot name="header">
        {{ __('Admin Theme') }}
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
