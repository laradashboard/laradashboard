@props([
    'id' => 'license-modal',
    'moduleSlug' => '',
    'moduleName' => '',
    'modalTrigger' => 'licenseModalOpen',
])

<template x-teleport="body">
    <div
        x-cloak
        x-show="{{ $modalTrigger }}"
        x-data="{
            licenseKey: '',
            isLoading: false,
            errorMessage: null,
            successMessage: null,
            licenseStatus: null,
            moduleSlug: '{{ $moduleSlug }}',
            moduleName: '{{ $moduleName }}',
            marketplaceUrl: '{{ config('laradashboard.marketplace.url', 'https://laradashboard.com') }}',

            clearMessages() {
                this.errorMessage = null;
                this.successMessage = null;
            },

            async activate() {
                this.clearMessages();

                if (!this.licenseKey || this.licenseKey.length < 10) {
                    this.errorMessage = '{{ __('Please enter a valid license key.') }}';
                    return;
                }

                this.isLoading = true;

                try {
                    // Call marketplace API to activate
                    const response = await fetch(`${this.marketplaceUrl}/api/licenses/activate`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            license_key: this.licenseKey,
                            module_slug: this.moduleSlug,
                            domain: window.location.hostname,
                        }),
                    });

                    const data = await response.json();

                    if (data.success) {
                        this.successMessage = data.message || '{{ __('License activated successfully.') }}';
                        this.licenseStatus = {
                            is_active: true,
                            license_key: this.licenseKey,
                            activated_at: data.data?.activated_at,
                        };

                        // Store locally in database via local API
                        await this.storeLicenseLocally();
                    } else {
                        this.errorMessage = data.message || '{{ __('Failed to activate license.') }}';
                    }
                } catch (error) {
                    this.errorMessage = '{{ __('Could not connect to license server. Please try again.') }}';
                    console.error('License activation error:', error);
                } finally {
                    this.isLoading = false;
                }
            },

            async deactivate() {
                if (!this.licenseKey) return;

                this.isLoading = true;
                this.clearMessages();

                try {
                    // Call marketplace API to deactivate
                    const response = await fetch(`${this.marketplaceUrl}/api/licenses/deactivate`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            license_key: this.licenseKey,
                            module_slug: this.moduleSlug,
                            domain: window.location.hostname,
                        }),
                    });

                    const data = await response.json();

                    if (data.success) {
                        // Remove locally from database via local API
                        await this.removeLicenseLocally();

                        this.licenseKey = '';
                        this.licenseStatus = null;
                        this.successMessage = data.message || '{{ __('License deactivated successfully.') }}';
                    } else {
                        this.errorMessage = data.message || '{{ __('Failed to deactivate license.') }}';
                    }
                } catch (error) {
                    this.errorMessage = '{{ __('Could not connect to license server.') }}';
                } finally {
                    this.isLoading = false;
                }
            },

            getCsrfToken() {
                return document.querySelector('meta[name=csrf-token]')?.content || '';
            },

            async storeLicenseLocally() {
                try {
                    await fetch('/api/admin/licenses/store', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': this.getCsrfToken(),
                        },
                        body: JSON.stringify({
                            license_key: this.licenseKey,
                            module_slug: this.moduleSlug,
                            module_name: this.moduleName,
                        }),
                    });
                } catch (error) {
                    console.error('Failed to store license locally:', error);
                }
            },

            async removeLicenseLocally() {
                try {
                    await fetch('/api/admin/licenses/remove', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': this.getCsrfToken(),
                        },
                        body: JSON.stringify({
                            module_slug: this.moduleSlug,
                        }),
                    });
                } catch (error) {
                    console.error('Failed to remove license locally:', error);
                }
            },

            async loadStoredLicense() {
                try {
                    // Try to load from database first via local API
                    const response = await fetch(`/api/admin/licenses/show?module_slug=${this.moduleSlug}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': this.getCsrfToken(),
                        },
                    });

                    if (response.ok) {
                        const result = await response.json();
                        if (result.data && result.data.license_key) {
                            this.licenseKey = result.data.license_key;
                            this.licenseStatus = {
                                is_active: true,
                                license_key: result.data.license_key,
                                activated_at: result.data.activated_at,
                            };
                            return;
                        }
                    }
                } catch (error) {
                    console.error('Failed to load license from database:', error);
                }
            },

            async init() {
                await this.loadStoredLicense();
            }
        }"
        x-init="init()"
        x-transition.opacity.duration.200ms
        x-trap.inert.noscroll="{{ $modalTrigger }}"
        x-on:keydown.esc.window="{{ $modalTrigger }} = false"
        x-on:click.self="{{ $modalTrigger }} = false"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/20 p-4 backdrop-blur-md"
        role="dialog"
        aria-modal="true"
        aria-labelledby="{{ $id }}-title"
    >
        <div
            x-show="{{ $modalTrigger }}"
            x-transition:enter="transition ease-out duration-200 delay-100 motion-reduce:transition-opacity"
            x-transition:enter-start="opacity-0 scale-50"
            x-transition:enter-end="opacity-100 scale-100"
            class="flex max-w-lg w-full flex-col overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-xl"
            @click.stop
        >
            {{-- Header --}}
            <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 px-4 py-3">
                <h3 id="{{ $id }}-title" class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <iconify-icon icon="lucide:key" width="20"></iconify-icon>
                    {{ __('License for :module', ['module' => $moduleName]) }}
                </h3>
                <button
                    x-on:click="{{ $modalTrigger }} = false"
                    aria-label="{{ __('Close modal') }}"
                    class="flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md p-1 transition-colors"
                >
                    <iconify-icon icon="lucide:x" width="20"></iconify-icon>
                </button>
            </div>

            {{-- Body --}}
            <div class="p-4">
                <template x-if="licenseStatus && licenseStatus.is_active">
                    <div class="space-y-4">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                <iconify-icon icon="lucide:check-circle" width="14" class="mr-1"></iconify-icon>
                                {{ __('Active') }}
                            </span>
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3">
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">{{ __('License Key') }}</p>
                            <p class="font-mono text-sm text-gray-900 dark:text-white break-all" x-text="licenseKey.substring(0, 8) + '...' + licenseKey.substring(licenseKey.length - 4)"></p>
                        </div>

                        <div
                            x-show="successMessage"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 -translate-y-1"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            class="p-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg text-green-700 dark:text-green-400 text-sm flex items-center gap-2"
                        >
                            <iconify-icon icon="lucide:check-circle" width="16" class="shrink-0"></iconify-icon>
                            <span x-text="successMessage"></span>
                        </div>

                        <div class="flex justify-end pt-3 border-t border-gray-200 dark:border-gray-700">
                            <button
                                type="button"
                                x-on:click="if(confirm('{{ __('Are you sure you want to deactivate this license?') }}')) deactivate()"
                                :disabled="isLoading"
                                class="inline-flex items-center px-4 py-2 text-sm font-medium text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors disabled:opacity-50"
                            >
                                <iconify-icon icon="lucide:x-circle" width="16" class="mr-2"></iconify-icon>
                                <span x-show="!isLoading">{{ __('Deactivate License') }}</span>
                                <span x-show="isLoading">{{ __('Deactivating...') }}</span>
                            </button>
                        </div>
                    </div>
                </template>

                <template x-if="!licenseStatus || !licenseStatus.is_active">
                    <div class="space-y-4">
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            {{ __('Enter your license key to activate this module and receive updates and support.') }}
                        </p>

                        {{-- Only show one message at a time, error takes priority --}}
                        <div
                            x-show="errorMessage"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 -translate-y-1"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 -translate-y-1"
                            class="p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg text-red-700 dark:text-red-400 text-sm flex items-center gap-2"
                        >
                            <iconify-icon icon="lucide:alert-circle" width="16" class="shrink-0"></iconify-icon>
                            <span x-text="errorMessage"></span>
                        </div>

                        <div
                            x-show="successMessage && !errorMessage"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 -translate-y-1"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 -translate-y-1"
                            class="p-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg text-green-700 dark:text-green-400 text-sm flex items-center gap-2"
                        >
                            <iconify-icon icon="lucide:check-circle" width="16" class="shrink-0"></iconify-icon>
                            <span x-text="successMessage"></span>
                        </div>

                        <div>
                            <label for="licenseKey-{{ $moduleSlug }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                {{ __('License Key') }}
                            </label>
                            <input
                                type="text"
                                id="licenseKey-{{ $moduleSlug }}"
                                x-model="licenseKey"
                                @input="clearMessages()"
                                placeholder="XXXX-XXXX-XXXX-XXXX-XXXX"
                                class="form-control font-mono text-sm"
                                :disabled="isLoading"
                            >
                        </div>

                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            {{ __('You can find your license key in your purchase confirmation email or in your account dashboard at') }}
                            <a href="{{ config('laradashboard.marketplace.url', 'https://laradashboard.com') }}" target="_blank" class="text-primary hover:underline">
                                {{ parse_url(config('laradashboard.marketplace.url', 'https://laradashboard.com'), PHP_URL_HOST) }}
                            </a>
                        </p>

                        <div class="flex justify-end pt-2 border-t border-gray-200 dark:border-gray-700">
                            <button
                                type="button"
                                x-on:click="activate()"
                                :disabled="isLoading || !licenseKey"
                                class="btn-primary disabled:opacity-50"
                            >
                                <iconify-icon icon="lucide:key" width="16" class="mr-2"></iconify-icon>
                                <span x-show="!isLoading">{{ __('Activate License') }}</span>
                                <span x-show="isLoading">{{ __('Activating...') }}</span>
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</template>
