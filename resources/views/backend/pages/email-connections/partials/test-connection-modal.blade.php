<div x-data="testConnectionModal()" x-init="init()">
    <div
        x-cloak
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @keydown.escape.window="open && closeModal()"
        @click.self="closeModal()"
        class="fixed inset-0 flex items-center justify-center bg-black/20 p-4 backdrop-blur-sm"
        role="dialog"
        aria-modal="true"
        style="display: none; z-index: 10000;"
    >
        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-200 delay-100"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            class="flex max-w-md w-full flex-col gap-4 overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-xl"
        >
            <!-- Header -->
            <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ __('Send Test Email') }}
                    </h3>
                    <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400" x-text="connectionName"></p>
                </div>
                <button type="button" @click="closeModal()" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                    <iconify-icon icon="lucide:x" class="text-xl"></iconify-icon>
                </button>
            </div>

            <!-- Body -->
            <div class="px-6">
                <form @submit.prevent="sendTestEmail()">
                    <div>
                        <label class="form-label">{{ __('Email Address') }} <span class="text-red-500">*</span></label>
                        <input
                            type="email"
                            x-ref="emailInput"
                            x-model="testEmail"
                            required
                            class="form-control"
                            placeholder="test@example.com"
                        >
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            {{ __('Enter the email address to send a test email to.') }}
                        </p>
                    </div>

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
            <div class="flex items-center justify-end gap-3 border-t border-gray-200 px-6 py-4 dark:border-gray-700">
                <button type="button" @click="closeModal()" class="btn-default">
                    {{ __('Cancel') }}
                </button>
                <button
                    type="button"
                    @click="sendTestEmail()"
                    :disabled="loading"
                    class="btn-primary"
                    :class="{ 'opacity-50 cursor-not-allowed': loading }"
                >
                    <template x-if="!loading">
                        <span>
                            <iconify-icon icon="lucide:send" class="mr-2"></iconify-icon>
                            {{ __('Send Test Email') }}
                        </span>
                    </template>
                    <template x-if="loading">
                        <span>{{ __('Sending...') }}</span>
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
        Alpine.data('testConnectionModal', () => ({
            open: false,
            loading: false,
            connectionId: null,
            connectionName: '',
            testEmail: '',
            errorMessage: '',
            successMessage: '',

            init() {
                window.addEventListener('open-test-connection-modal', (event) => {
                    this.connectionId = event.detail?.id;
                    this.connectionName = event.detail?.name || '';
                    this.open = true;
                });

                this.$watch('open', (value) => {
                    if (!value) {
                        this.resetForm();
                    }
                });
            },

            closeModal() {
                this.open = false;
            },

            resetForm() {
                this.testEmail = '';
                this.errorMessage = '';
                this.successMessage = '';
                this.loading = false;
            },

            async sendTestEmail() {
                if (!this.testEmail) {
                    this.errorMessage = '{{ __('Please enter an email address.') }}';
                    return;
                }

                this.loading = true;
                this.errorMessage = '';
                this.successMessage = '';

                try {
                    const response = await fetch(`{{ route('admin.email-connections.index') }}/${this.connectionId}/test`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ email: this.testEmail })
                    });

                    const data = await response.json();

                    if (data.success) {
                        this.successMessage = data.message;
                        setTimeout(() => {
                            this.closeModal();
                            Livewire.dispatch('refreshDatatable');
                        }, 2000);
                    } else {
                        this.errorMessage = data.message || '{{ __('Failed to send test email.') }}';
                    }
                } catch (error) {
                    console.error('Error sending test email:', error);
                    this.errorMessage = '{{ __('An error occurred while sending the test email.') }}';
                } finally {
                    this.loading = false;
                }
            }
        }));
    });
</script>
@endpush
@endonce
