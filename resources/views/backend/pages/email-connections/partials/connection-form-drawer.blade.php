<div x-data="connectionFormDrawer()" x-init="init()">
    <!-- Overlay -->
    <div
        x-cloak
        x-show="open"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-300"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="closeDrawer()"
        class="fixed inset-0 bg-gray-900/30 backdrop-blur-sm z-40"
        style="display: none;"
    ></div>

    <!-- Drawer -->
    <div
        x-cloak
        x-show="open"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-300"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        @click.stop
        class="fixed top-0 right-0 bottom-0 w-full sm:w-[540px] max-w-full z-50 flex flex-col bg-white dark:bg-gray-800 shadow-xl border-l border-gray-200 dark:border-gray-700"
        style="display: none;"
    >
        <!-- Header -->
        <div class="px-6 py-4 flex justify-between items-center border-b border-gray-200 dark:border-gray-700 flex-shrink-0">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    <span x-text="isEditing ? '{{ __('Edit Connection') }}' : '{{ __('Connection Details') }}'"></span>
                </h3>
                <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400" x-show="provider">
                    <span x-text="provider?.name"></span>
                </p>
            </div>
            <button type="button" @click="closeDrawer()" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                <iconify-icon icon="lucide:x" class="text-xl"></iconify-icon>
            </button>
        </div>

        <!-- Body -->
        <div class="flex-1 overflow-y-auto p-6">
            <form @submit.prevent="submitForm()" id="connection-form">
                <!-- Common Fields -->
                <div class="space-y-4">
                    <div>
                        <label class="form-label">{{ __('Connection Title') }} <span class="text-red-500">*</span></label>
                        <input type="text" x-model="formData.name" class="form-control" required placeholder="{{ __('My Email Connection') }}">
                    </div>

                    <div>
                        <label class="form-label">{{ __('From Email') }} <span class="text-red-500">*</span></label>
                        <input type="email" x-model="formData.from_email" @input="onFromEmailInput()" class="form-control" required placeholder="{{ __('noreply@example.com') }}">
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('The email address that emails will be sent from.') }}</p>
                    </div>

                    <div class="flex items-start gap-2">
                        <input type="checkbox" x-model="formData.force_from_email" class="form-checkbox mt-1" id="force_from_email">
                        <div>
                            <label for="force_from_email" class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Force From Email') }}</label>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Enable this option to force all emails sent from your site to use this email address.') }}</p>
                        </div>
                    </div>

                    <div>
                        <label class="form-label">{{ __('From Name') }}</label>
                        <input type="text" x-model="formData.from_name" @input="onFromNameInput()" class="form-control" placeholder="{{ __('My Company') }}">
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('The name that emails will be sent from.') }}</p>
                    </div>

                    <div class="flex items-start gap-2">
                        <input type="checkbox" x-model="formData.force_from_name" class="form-checkbox mt-1" id="force_from_name">
                        <div>
                            <label for="force_from_name" class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Force From Name') }}</label>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Enable this option to ensure all emails sent from your site use this name.') }}</p>
                        </div>
                    </div>

                    <div>
                        <label class="form-label">{{ __('Priority') }} <span class="text-red-500">*</span></label>
                        <input type="number" x-model="formData.priority" class="form-control" min="1" max="1000" placeholder="10">
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('Set the order in which connections should be used. Lower numbers = higher priority.') }}</p>
                    </div>
                </div>

                <!-- Provider-specific Fields -->
                <template x-if="provider && provider.fields && provider.fields.length > 0">
                    <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">
                            <span x-text="provider.name"></span> {{ __('Settings') }}
                        </h4>
                        <div class="space-y-4">
                            <template x-for="field in provider.fields" :key="field.name">
                                <div>
                                    <label class="form-label">
                                        <span x-text="field.label"></span>
                                        <span x-show="field.required" class="text-red-500">*</span>
                                    </label>

                                    <!-- Text/Email Input (Credential) -->
                                    <template x-if="(field.type === 'text' || field.type === 'email') && field.is_credential">
                                        <input
                                            :type="field.type"
                                            x-model="formData.credentials[field.name]"
                                            class="form-control"
                                            :required="field.required"
                                            :placeholder="field.placeholder"
                                        >
                                    </template>

                                    <!-- Text/Email Input (Setting) -->
                                    <template x-if="(field.type === 'text' || field.type === 'email') && !field.is_credential">
                                        <input
                                            :type="field.type"
                                            x-model="formData.settings[field.name]"
                                            class="form-control"
                                            :required="field.required"
                                            :placeholder="field.placeholder"
                                        >
                                    </template>

                                    <!-- Password Input -->
                                    <template x-if="field.type === 'password'">
                                        <input
                                            type="password"
                                            x-model="formData.credentials[field.name]"
                                            class="form-control"
                                            :required="field.required"
                                            :placeholder="field.placeholder"
                                            autocomplete="new-password"
                                        >
                                    </template>

                                    <!-- Number Input (Credential) -->
                                    <template x-if="field.type === 'number' && field.is_credential">
                                        <input
                                            type="number"
                                            x-model="formData.credentials[field.name]"
                                            class="form-control"
                                            :required="field.required"
                                            :placeholder="field.placeholder"
                                        >
                                    </template>

                                    <!-- Number Input (Setting) -->
                                    <template x-if="field.type === 'number' && !field.is_credential">
                                        <input
                                            type="number"
                                            x-model="formData.settings[field.name]"
                                            class="form-control"
                                            :required="field.required"
                                            :placeholder="field.placeholder"
                                        >
                                    </template>

                                    <!-- Select Input -->
                                    <template x-if="field.type === 'select'">
                                        <select
                                            x-model="formData.settings[field.name]"
                                            class="form-control"
                                            :required="field.required"
                                        >
                                            <template x-for="option in field.options" :key="option.value">
                                                <option :value="option.value" x-text="option.label"></option>
                                            </template>
                                        </select>
                                    </template>

                                    <p x-show="field.help" class="mt-1 text-xs text-gray-500 dark:text-gray-400" x-text="field.help"></p>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

                <!-- Error Message -->
                <div x-show="errorMessage" x-cloak class="mt-4 p-3 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-md">
                    <p class="text-sm text-red-600 dark:text-red-400" x-text="errorMessage"></p>
                </div>

                <!-- Success Message -->
                <div x-show="successMessage" x-cloak class="mt-4 p-3 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-md">
                    <p class="text-sm text-green-600 dark:text-green-400" x-text="successMessage"></p>
                </div>
            </form>
        </div>

        <!-- Footer -->
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex-shrink-0">
            <div class="flex justify-between items-center">
                <button type="button" @click="goBack()" class="btn-default" x-show="!isEditing">
                    <iconify-icon icon="lucide:arrow-left" class="mr-2"></iconify-icon>
                    {{ __('Back') }}
                </button>
                <button type="button" @click="closeDrawer()" class="btn-default" x-show="isEditing">
                    {{ __('Cancel') }}
                </button>
                <button type="submit" form="connection-form" class="btn-primary" :disabled="loading">
                    <template x-if="!loading">
                        <span>{{ __('Save Changes') }}</span>
                    </template>
                    <template x-if="loading">
                        <span>{{ __('Saving...') }}</span>
                    </template>
                </button>
            </div>
        </div>
    </div>
</div>

@once
@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('connectionFormDrawer', () => ({
            open: false,
            loading: false,
            errorMessage: '',
            successMessage: '',
            provider: null,
            formData: {
                name: '',
                from_email: '',
                from_name: '',
                force_from_email: false,
                force_from_name: false,
                is_active: true,
                is_default: false,
                priority: 10,
                settings: {},
                credentials: {}
            },

            get isEditing() {
                const store = window.getEmailConnectionStore();
                return store && store.editingId !== null;
            },

            init() {
                window.addEventListener('open-connection-form', async () => {
                    await this.loadProviderFields();
                    this.open = true;
                });

                this.$watch('open', (value) => {
                    if (!value) {
                        this.resetForm();
                    }
                });
            },

            async loadProviderFields() {
                const store = window.getEmailConnectionStore();
                if (!store) {
                    console.error('Email connection store not available');
                    return;
                }
                const providerType = store.providerType;

                if (!providerType) return;

                try {
                    const response = await fetch(`{{ route('admin.email-connections.providers') }}/${providerType}`);
                    const data = await response.json();
                    this.provider = data.provider;

                    // If editing, load existing data first
                    if (store.editingId) {
                        this.formData = { ...this.formData, ...store.formData };
                        // Ensure credentials object exists
                        if (!this.formData.credentials) {
                            this.formData.credentials = {};
                        }
                        if (!this.formData.settings) {
                            this.formData.settings = {};
                        }
                    } else {
                        // Auto-generate default connection name for new connections
                        this.formData.name = this.provider ? `${this.provider.name} Connection` : '';
                    }

                    // Initialize ALL fields (not just those with defaults) to ensure Alpine reactivity
                    if (this.provider && this.provider.fields) {
                        this.provider.fields.forEach(field => {
                            if (field.is_credential) {
                                // Only set if not already set (preserve existing values when editing)
                                if (this.formData.credentials[field.name] === undefined) {
                                    this.formData.credentials[field.name] = field.default ?? '';
                                }
                            } else {
                                // Only set if not already set (preserve existing values when editing)
                                if (this.formData.settings[field.name] === undefined) {
                                    this.formData.settings[field.name] = field.default ?? '';
                                }
                            }
                        });
                    }
                } catch (error) {
                    console.error('Error loading provider fields:', error);
                }
            },

            // Auto-check force options when values are entered
            onFromEmailInput() {
                if (this.formData.from_email && this.formData.from_email.length > 0) {
                    this.formData.force_from_email = true;
                }
            },

            onFromNameInput() {
                if (this.formData.from_name && this.formData.from_name.length > 0) {
                    this.formData.force_from_name = true;
                }
            },

            goBack() {
                this.closeDrawer();
                window.dispatchEvent(new CustomEvent('open-provider-selector'));
            },

            closeDrawer() {
                this.open = false;
            },

            resetForm() {
                this.errorMessage = '';
                this.successMessage = '';
                this.provider = null;
                this.formData = {
                    name: '',
                    from_email: '',
                    from_name: '',
                    force_from_email: false,
                    force_from_name: false,
                    is_active: true,
                    is_default: false,
                    priority: 10,
                    settings: {},
                    credentials: {}
                };
                window.getEmailConnectionStore().reset();
            },

            async submitForm() {
                this.loading = true;
                this.errorMessage = '';
                this.successMessage = '';

                const store = window.getEmailConnectionStore();
                const isEditing = store && store.editingId !== null;
                const url = isEditing
                    ? `{{ route('admin.email-connections.index') }}/${store.editingId}`
                    : `{{ route('admin.email-connections.store') }}`;

                try {
                    const response = await fetch(url, {
                        method: isEditing ? 'PUT' : 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            ...this.formData,
                            provider_type: store.providerType
                        })
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        this.successMessage = data.message;
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    } else {
                        // Handle validation errors
                        if (data.errors) {
                            const firstError = Object.values(data.errors)[0];
                            this.errorMessage = Array.isArray(firstError) ? firstError[0] : firstError;
                        } else {
                            this.errorMessage = data.message || '{{ __('An error occurred while saving.') }}';
                        }
                    }
                } catch (error) {
                    console.error('Error saving connection:', error);
                    this.errorMessage = '{{ __('An error occurred while saving.') }}';
                } finally {
                    this.loading = false;
                }
            }
        }));
    });
</script>
@endpush
@endonce
