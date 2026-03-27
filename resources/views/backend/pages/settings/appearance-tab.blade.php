@php
    $pages = \App\Models\Post::where('post_type', 'page')
        ->where('status', 'published')
        ->orderBy('title')
        ->get(['id', 'title']);
@endphp

{!! Hook::applyFilters(SettingFilterHook::SETTINGS_APPEARANCE_TAB_BEFORE_SECTION_START, '') !!}

{{-- Frontend Pages --}}
<x-card>
    <x-slot name="header">
        {{ __('Frontend Pages') }}
    </x-slot>
    <x-slot name="headerDescription">
        {{ __('Configure which pages are used for your homepage and blog.') }}
    </x-slot>

    @php
        $pageOptions = [['value' => '', 'label' => __('— Default —')]];
        foreach ($pages as $page) {
            $pageOptions[] = ['value' => $page->id, 'label' => $page->title];
        }
    @endphp

    <div class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Homepage Picker --}}
            <div>
                <x-inputs.combobox
                    name="homepage_id"
                    :label="__('Homepage')"
                    :placeholder="__('Select a page')"
                    :options="$pageOptions"
                    :selected="config('settings.homepage_id', '')"
                    :searchable="true"
                    :hint="__('Select a page to use as your homepage.')"
                />
            </div>

            {{-- Blog Page Picker --}}
            <div>
                <x-inputs.combobox
                    name="blog_page_id"
                    :label="__('Blog Page')"
                    :placeholder="__('Select a page')"
                    :options="$pageOptions"
                    :selected="config('settings.blog_page_id', '')"
                    :searchable="true"
                    :hint="__('Select a page for the blog listing.')"
                />
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Blog URL Prefix --}}
            <div>
                <x-inputs.input
                    name="blog_url_prefix"
                    :label="__('Blog URL Prefix')"
                    :placeholder="__('blog')"
                    :value="config('settings.blog_url_prefix', 'blog')"
                    :hint="__('URL prefix for the blog page (e.g., /blog).')"
                />
            </div>

            {{-- Posts Per Page --}}
            <div>
                <x-inputs.input
                    type="number"
                    name="posts_per_page"
                    :label="__('Posts Per Page')"
                    :placeholder="__('12')"
                    :value="config('settings.posts_per_page', 12)"
                    :hint="__('Number of posts per page on the blog.')"
                    min="1"
                    max="100"
                />
            </div>
        </div>
    </div>
</x-card>

{!! Hook::applyFilters(SettingFilterHook::SETTINGS_SITE_IDENTITY_TAB_BEFORE_SECTION_START, '') !!}

{{-- Site Identity --}}
<x-card class="mt-6">
    <x-slot name="header">
        {{ __('Site Identity') }}
    </x-slot>
    <div class="space-y-6">
        {{-- Site Tagline --}}
        <div>
            <x-inputs.input
                name="site_tagline"
                :label="__('Site Tagline')"
                :placeholder="__('Your site tagline or slogan')"
                :value="config('settings.site_tagline', '')"
                :hint="__('A short description of your site, often displayed in the footer or header.')"
            />
        </div>

        {{-- Copyright Text --}}
        <div>
            <x-inputs.input
                name="copyright_text"
                :label="__('Copyright Text')"
                :placeholder="__('e.g., All rights reserved.')"
                :value="config('settings.copyright_text', '')"
                :hint="__('The copyright notice displayed in the footer. Use {year} for dynamic year.')"
            />
        </div>

        {{-- Footer Description --}}
        <div>
            <x-inputs.textarea
                name="footer_description"
                :label="__('Footer Description')"
                :placeholder="__('A short description displayed in the footer.')"
                :value="config('settings.footer_description', '')"
                :rows="3"
            />
        </div>
    </div>
</x-card>

{{-- Contact Information --}}
<x-card class="mt-6">
    <x-slot name="header">
        {{ __('Contact Information') }}
    </x-slot>
    <div class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Contact Email --}}
            <div>
                <x-inputs.input
                    type="email"
                    name="contact_email"
                    :label="__('Contact Email')"
                    :placeholder="__('contact@example.com')"
                    :value="config('settings.contact_email', '')"
                    addonLeftIcon="lucide:mail"
                />
            </div>

            {{-- Contact Phone --}}
            <div>
                <x-inputs.input
                    type="tel"
                    name="contact_phone"
                    :label="__('Contact Phone')"
                    :placeholder="__('+1 (555) 123-4567')"
                    :value="config('settings.contact_phone', '')"
                    addonLeftIcon="lucide:phone"
                />
            </div>
        </div>

        {{-- Contact Address --}}
        <div>
            <x-inputs.textarea
                name="contact_address"
                :label="__('Contact Address')"
                :placeholder="__('Enter your business address')"
                :value="config('settings.contact_address', '')"
                :rows="2"
            />
        </div>
    </div>
</x-card>

{{-- Social Links --}}
<x-card class="mt-6">
    <x-slot name="header">
        {{ __('Social Links') }}
    </x-slot>
    <div
        x-data="{
            links: @js(json_decode(config('settings.social_links', '[]'), true) ?: []),
            platforms: [
                { value: 'facebook', label: 'Facebook', icon: 'mdi:facebook' },
                { value: 'twitter', label: 'Twitter / X', icon: 'mdi:twitter' },
                { value: 'instagram', label: 'Instagram', icon: 'mdi:instagram' },
                { value: 'linkedin', label: 'LinkedIn', icon: 'mdi:linkedin' },
                { value: 'youtube', label: 'YouTube', icon: 'mdi:youtube' },
                { value: 'github', label: 'GitHub', icon: 'mdi:github' },
                { value: 'tiktok', label: 'TikTok', icon: 'ic:baseline-tiktok' },
                { value: 'pinterest', label: 'Pinterest', icon: 'mdi:pinterest' },
                { value: 'discord', label: 'Discord', icon: 'ic:baseline-discord' },
                { value: 'whatsapp', label: 'WhatsApp', icon: 'mdi:whatsapp' },
                { value: 'telegram', label: 'Telegram', icon: 'mdi:telegram' },
                { value: 'other', label: '{{ __('Other') }}', icon: 'lucide:link' }
            ],
            addLink() {
                this.links.push({ platform: 'facebook', url: '' });
            },
            removeLink(index) {
                this.links.splice(index, 1);
            },
            getPlatformIcon(platform) {
                const p = this.platforms.find(item => item.value === platform);
                return p ? p.icon : 'lucide:link';
            },
            getPlatformLabel(platform) {
                const p = this.platforms.find(item => item.value === platform);
                return p ? p.label : platform;
            }
        }"
        class="space-y-4"
    >
        {{-- Hidden field to store JSON data --}}
        <input type="hidden" name="social_links" :value="JSON.stringify(links)">

        {{-- Social Links List --}}
        <template x-for="(link, index) in links" :key="index">
            <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                {{-- Platform Icon --}}
                <div class="flex-shrink-0">
                    <iconify-icon :icon="getPlatformIcon(link.platform)" class="text-2xl text-gray-600 dark:text-gray-400"></iconify-icon>
                </div>

                {{-- Platform Select --}}
                <div class="w-40 flex-shrink-0">
                    <select
                        x-model="link.platform"
                        class="form-control text-sm"
                    >
                        <template x-for="platform in platforms" :key="platform.value">
                            <option :value="platform.value" x-text="platform.label"></option>
                        </template>
                    </select>
                </div>

                {{-- URL Input --}}
                <div class="flex-1">
                    <input
                        type="url"
                        x-model="link.url"
                        :placeholder="'https://'"
                        class="form-control text-sm"
                    >
                </div>

                {{-- Remove Button --}}
                <button
                    type="button"
                    @click="removeLink(index)"
                    class="flex-shrink-0 p-2 text-red-500 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-md transition-colors"
                    title="{{ __('Remove') }}"
                >
                    <iconify-icon icon="lucide:trash-2" class="text-lg"></iconify-icon>
                </button>
            </div>
        </template>

        {{-- Empty State --}}
        <div x-show="links.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
            <iconify-icon icon="lucide:share-2" class="text-4xl mb-2"></iconify-icon>
            <p>{{ __('No social links added yet.') }}</p>
        </div>

        {{-- Add Button --}}
        <div class="flex justify-center">
            <button
                type="button"
                @click="addLink()"
                class="btn btn-default"
            >
                <iconify-icon icon="lucide:plus" class="mr-2"></iconify-icon>
                {{ __('Add Social Link') }}
            </button>
        </div>
    </div>
</x-card>

{!! Hook::applyFilters(SettingFilterHook::SETTINGS_SITE_IDENTITY_TAB_BEFORE_SECTION_END, '') !!}
{!! Hook::applyFilters(SettingFilterHook::SETTINGS_SITE_IDENTITY_TAB_AFTER_SECTION_END, '') !!}

{{-- Admin Theme --}}
<x-card class="mt-6">
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
