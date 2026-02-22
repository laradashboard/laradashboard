{!! Hook::applyFilters(SettingFilterHook::SETTINGS_SITE_IDENTITY_TAB_BEFORE_SECTION_START, '') !!}

<x-card>
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
    </div>
</x-card>

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
        <button
            type="button"
            @click="addLink()"
            class="btn btn-default w-full"
        >
            <iconify-icon icon="lucide:plus" class="mr-2"></iconify-icon>
            {{ __('Add Social Link') }}
        </button>
    </div>
</x-card>

{!! Hook::applyFilters(SettingFilterHook::SETTINGS_SITE_IDENTITY_TAB_BEFORE_SECTION_END, '') !!}
{!! Hook::applyFilters(SettingFilterHook::SETTINGS_SITE_IDENTITY_TAB_AFTER_SECTION_END, '') !!}
