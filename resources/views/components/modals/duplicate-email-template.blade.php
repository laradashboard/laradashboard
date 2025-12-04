@props([
    'templateId' => null,
    'duplicateUrl' => null,
])

<div x-data="duplicateEmailTemplateModal(@js($templateId), @js($duplicateUrl))">
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
        aria-labelledby="duplicate-email-template-modal-title"
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
                <h3 id="duplicate-email-template-modal-title" class="font-semibold tracking-wide text-gray-700 dark:text-white">
                    {{ __('Duplicate Email Template') }}
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
                <form :action="duplicateUrl" method="POST">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">

                    <div class="mb-4">
                        <label for="duplicateNameInput" class="form-label">
                            {{ __('New Template Name') }}
                        </label>
                        <input
                            type="text"
                            id="duplicateNameInput"
                            name="name"
                            x-ref="nameInput"
                            required
                            class="form-control"
                            placeholder="{{ __('New template name') }}"
                        >
                    </div>
                </form>

                <div x-show="errorMessage" x-cloak class="mb-4 p-3 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-md">
                    <p class="text-sm text-red-600 dark:text-red-400" x-text="errorMessage"></p>
                </div>
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
                    @click="submit()"
                    :disabled="loading"
                    class="btn-primary"
                    :class="{ 'opacity-50 cursor-not-allowed': loading }"
                >
                    <template x-if="!loading">
                        <span>{{ __('Duplicate') }}</span>
                    </template>
                    <template x-if="loading">
                        <span>{{ __('Duplicating...') }}</span>
                    </template>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function openDuplicateEmailTemplateModal(id, url) {
        window.dispatchEvent(new CustomEvent('open-duplicate-email-template-modal', {
            detail: { id, url }
        }));
    }

    document.addEventListener('alpine:init', () => {
        Alpine.data('duplicateEmailTemplateModal', (initialTemplateId = null, initialDuplicateUrl = null) => ({
            open: false,
            loading: false,
            errorMessage: '',
            templateId: initialTemplateId,
            duplicateUrl: initialDuplicateUrl,

            init() {
                this.$watch('open', value => {
                    if (!value) {
                        this.errorMessage = '';
                        this.loading = false;
                    }
                });

                window.addEventListener('open-duplicate-email-template-modal', (event) => {
                    this.templateId = event.detail?.id || this.templateId;
                    this.duplicateUrl = event.detail?.url || this.duplicateUrl;
                    this.open = true;

                    // Close any open dropdowns when modal opens
                    window.dispatchEvent(new CustomEvent('click'));
                });
            },

            closeModal() {
                this.open = false;
                if (this.$refs?.nameInput) {
                    this.$refs.nameInput.value = '';
                }
            },

            submit() {
                console.log('templateId', this.templateId);
                console.log('duplicateUrl', this.duplicateUrl);
                if (!this.duplicateUrl && !this.templateId) {
                    this.errorMessage = @json(__('Template not selected'));
                    return;
                }

                const name = this.$refs.nameInput?.value || '';
                if (!name) {
                    this.errorMessage = @json(__('Please enter a name for the duplicated template'));
                    return;
                }

                this.loading = true;

                // Determine URL
                const url = this.duplicateUrl || `/admin/settings/email-templates/${this.templateId}/duplicate`;

                // Submit form by creating a temporary form and posting to the duplicate URL
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = url;

                const tokenInput = document.createElement('input');
                tokenInput.type = 'hidden';
                tokenInput.name = '_token';
                tokenInput.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                form.appendChild(tokenInput);

                const nameInput = document.createElement('input');
                nameInput.type = 'hidden';
                nameInput.name = 'name';
                nameInput.value = name;
                form.appendChild(nameInput);

                document.body.appendChild(form);
                form.submit();
            }
        }));
    });
</script>
