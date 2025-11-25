<div x-data="testEmailModal()" x-init="init()">
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
        class="fixed inset-0 flex items-center justify-center bg-black/20 p-4 backdrop-blur-md"
        role="dialog"
        aria-modal="true"
        aria-labelledby="test-email-modal-title"
        style="display: none; z-index: 10000;"
    >
        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-200 delay-100 motion-reduce:transition-opacity"
            x-transition:enter-start="opacity-0 scale-50"
            x-transition:enter-end="opacity-100 scale-100"
            class="flex max-w-md w-full flex-col gap-4 overflow-hidden rounded-md border border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-700"
        >
            <!-- Header -->
            <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3 dark:border-gray-800">
                <h3 id="test-email-modal-title" class="font-semibold tracking-wide text-gray-700 dark:text-white">
                    {{ __('Send Test Email') }}
                </h3>
                <button
                    type="button"
                    @click="closeModal()"
                    aria-label="close modal"
                    class="text-gray-400 hover:bg-gray-200 hover:text-gray-700 rounded-md p-1 dark:hover:bg-gray-600 dark:hover:text-white"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="1.4" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Body -->
            <div class="px-4">
                <form @submit.prevent="sendTestEmail()">
                    <div class="mb-4">
                        <label for="testEmailInput" class="form-label">
                            {{ __('Email Address') }}
                        </label>
                        <input
                            type="email"
                            id="testEmailInput"
                            x-ref="emailInput"
                            required
                            class="form-control"
                            placeholder="test@example.com"
                        >
                    </div>
                    
                    <!-- Error Message -->
                    <div x-show="errorMessage" x-cloak class="mb-4 p-3 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-md">
                        <p class="text-sm text-red-600 dark:text-red-400" x-text="errorMessage"></p>
                    </div>
                    
                    <!-- Success Message -->
                    <div x-show="successMessage" x-cloak class="mb-4 p-3 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-md">
                        <p class="text-sm text-green-600 dark:text-green-400" x-text="successMessage"></p>
                    </div>
                </form>
            </div>

            <!-- Footer -->
            <div class="flex items-center justify-end gap-3 border-t border-gray-100 p-4 dark:border-gray-800">
                <button
                    type="button"
                    @click="closeModal()"
                    class="btn-default"
                >
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
                        <span>{{ __('Send Test Email') }}</span>
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
    function openTestEmailModal(id, type) {
        window.dispatchEvent(new CustomEvent('open-test-email-modal', {
            detail: { id, type }
        }));
    }

    document.addEventListener('alpine:init', () => {
        Alpine.data('testEmailModal', () => ({
            open: false,
            loading: false,
            errorMessage: '',
            successMessage: '',
            id: null,
            type: 'email-template',

            init() {
                this.$watch('open', value => {
                    if (!value) {
                        this.errorMessage = '';
                        this.successMessage = '';
                        this.loading = false;
                    }
                });

                window.addEventListener('open-test-email-modal', (event) => {
                    this.type = event.detail?.type || 'email-template';
                    this.id = event.detail?.id;
                    this.open = true;

                    // Close any open dropdowns when modal opens
                    window.dispatchEvent(new CustomEvent('click'));
                });
            },

            closeModal() {
                this.open = false;
                if (this.$refs.emailInput) {
                    this.$refs.emailInput.value = '';
                }
            },
            
            sendTestEmail() {
                const email = this.$refs.emailInput.value;
                
                if (!email) {
                    this.errorMessage = '{{ __('Please enter an email address') }}';
                    return;
                }

                this.loading = true;
                this.errorMessage = '';
                this.successMessage = '';

                const url = `/admin/settings/emails/send-test?type=${this.type}&id=${this.id}`;

                fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ email: email })
                })
                .then(response => response.json())
                .then(data => {
                    if (data?.message) {
                        this.successMessage = data.message;
                        setTimeout(() => {
                            this.closeModal();
                        }, 2000);
                    } else {
                        this.errorMessage = '{{ __('Failed to send test email') }}';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    this.errorMessage = '{{ __('An error occurred while sending the test email') }}';
                })
                .finally(() => {
                    this.loading = false;
                });
            }
        }));
    });
</script>
@endpush
@endonce
