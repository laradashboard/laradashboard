<form
    method="POST"
    action="{{ isset($notification) ? route('admin.notifications.update', $notification->id) : route('admin.notifications.store') }}"
    data-prevent-unsaved-changes
    x-data="notificationForm()"
>
    @csrf
    @if (isset($notification))
        @method('PUT')
    @endif

    <div class="flex flex-col lg:flex-row gap-6">
        <div class="w-full lg:w-80 lg:flex-shrink-0 space-y-6">
            <x-card class="sticky top-24">
                <x-inputs.combobox label="{{ __('Notification Type') }}" name="notification_type" :options="$notificationTypes ?? []"
                    placeholder="{{ __('Select Notification Type') }}" selected="{{ old('notification_type', $notification->notification_type ?? '') }}"
                    required />

                <x-inputs.combobox
                    label="{{ __('Email Template') }}"
                    name="email_template_id"
                    :options="$emailTemplates ?? []"
                    placeholder="{{ __('Select Email Template') }}"
                    selected="{{ old('email_template_id', $notification->email_template_id ?? '') }}"
                    required
                />

                <div id="receiver-settings" class="flex flex-col gap-3">
                    <x-inputs.combobox
                        :label="__('Receiver Type')"
                        name="receiver_type"
                        :options="$receiverTypes ?? []"
                        placeholder="{{ __('Select Receiver Type') }}"
                        selected="{{ old('receiver_type', $notification->receiver_type ?? '') }}"
                        required
                    />

                    <div id="receiver_ids_field" class="hidden">
                        <x-inputs.textarea
                            name="receiver_ids_text"
                            :label="__('Receiver IDs')"
                            :value="old('receiver_ids_text', isset($notification) ? implode(',', $notification->receiver_ids ?? []) : '')"
                            placeholder="{{ __('Enter comma-separated IDs') }}"
                        />
                    </div>

                    <div id="receiver_emails_field" class="hidden">
                        <x-inputs.textarea
                            name="receiver_emails_text"
                            :label="__('Email Addresses')"
                            :value="old('receiver_emails_text', isset($notification) ? implode(',', $notification->receiver_emails ?? []) : '')"
                            rows="3"
                            placeholder="{{ __('Enter email addresses, one per line or comma-separated') }}"
                        />
                    </div>
                </div>

                <div class="pt-2">
                    <label class="flex items-center justify-between cursor-pointer group">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Active Status') }}</span>
                        <div>
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" id="is_active" name="is_active" value="1" class="sr-only peer" {{ old('is_active', $notification->is_active ?? true) ? 'checked' : '' }}>
                            <div class="relative w-11 h-6 bg-gray-200 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary"></div>
                        </div>
                    </label>
                </div>

                <x-buttons.submit-buttons
                    cancelUrl="{{ route('admin.notifications.index') }}"
                    :classNames="['wrapper' => 'grid grid-cols-2 gap-3']"
                />
            </x-card>
        </div>

        <div class="flex-1 min-w-0">
            <x-card>
                <div class="space-y-5">
                    <x-inputs.input
                        label="{{ __('Notification Name') }}"
                        name="name"
                        type="text"
                        :value="old('name', $notification->name ?? '')"
                        placeholder="{{ __('e.g., Forgot Password Notification, Welcome Email') }}"
                        required
                    />

                    <x-inputs.textarea
                        label="{{ __('Internal Description (Optional)') }}"
                        name="description"
                        :value="old('description', $notification->description ?? '')"
                        rows="3"
                        placeholder="{{ __('Brief description of this notification...') }}"
                        class="min-h-20"
                    />
                </div>
            </x-card>

            {{-- Email Template Preview Section --}}
            <x-card class="mt-6">
                <x-slot name="header">
                    <div class="flex items-center gap-2">
                        <iconify-icon icon="lucide:mail" class="text-primary"></iconify-icon>
                        {{ __('Email Template Preview') }}
                    </div>
                </x-slot>
                <x-slot name="headerRight">
                    <template x-if="selectedTemplateId">
                        <a :href="`{{ route('admin.email-templates.index') }}/${selectedTemplateId}/edit`"
                           class="btn-primary text-sm"
                           target="_blank">
                            <iconify-icon icon="feather:edit-2" class="mr-1.5"></iconify-icon>
                            {{ __('Edit Template') }}
                        </a>
                    </template>
                </x-slot>

                <div x-show="!selectedTemplateId" class="text-center py-12">
                    <iconify-icon icon="lucide:mail-question" class="text-5xl text-gray-300 dark:text-gray-600 mb-4"></iconify-icon>
                    <p class="text-gray-500 dark:text-gray-400">{{ __('Select an email template to see preview') }}</p>
                </div>

                <div x-show="selectedTemplateId && loading" class="text-center py-12">
                    <iconify-icon icon="lucide:loader-2" class="text-3xl text-primary animate-spin"></iconify-icon>
                    <p class="text-gray-500 dark:text-gray-400 mt-2">{{ __('Loading template preview...') }}</p>
                </div>

                <div x-show="selectedTemplateId && !loading" x-cloak>
                    <div class="mb-4 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Template Name:') }}</span>
                            <span class="text-sm text-gray-600 dark:text-gray-400" x-text="templateData.name"></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Subject:') }}</span>
                            <span class="text-sm text-gray-600 dark:text-gray-400" x-text="templateData.subject"></span>
                        </div>
                    </div>

                    <div x-data="{ previewTab: 'preview' }" class="mb-4">
                        <div class="flex border-b border-gray-200 dark:border-gray-700 mb-4">
                            <button type="button"
                                @click="previewTab = 'preview'"
                                :class="previewTab === 'preview' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700'"
                                class="px-4 py-2 text-sm font-medium border-b-2 -mb-px transition-colors">
                                {{ __('Preview') }}
                            </button>
                            <button type="button"
                                @click="previewTab = 'source'"
                                :class="previewTab === 'source' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700'"
                                class="px-4 py-2 text-sm font-medium border-b-2 -mb-px transition-colors">
                                {{ __('Source Code') }}
                            </button>
                        </div>

                        <div x-show="previewTab === 'preview'" class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                            <iframe id="template-preview-iframe"
                                    class="w-full bg-white"
                                    style="min-height: 400px; max-height: 600px;"
                                    sandbox="allow-same-origin">
                            </iframe>
                        </div>

                        <div x-show="previewTab === 'source'" x-cloak>
                            <pre class="whitespace-pre-wrap font-mono text-xs bg-gray-50 dark:bg-gray-800 p-4 rounded-md text-gray-700 dark:text-gray-300 overflow-auto max-h-[500px] border border-gray-200 dark:border-gray-700"><code x-text="templateData.body_html"></code></pre>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                        <iconify-icon icon="lucide:info" class="text-blue-500"></iconify-icon>
                        <p class="text-sm text-blue-700 dark:text-blue-300">
                            {{ __('To modify the email content, please edit the email template directly.') }}
                            <template x-if="selectedTemplateId">
                                <a :href="`{{ route('admin.email-templates.index') }}/${selectedTemplateId}/edit`"
                                   class="font-medium underline hover:no-underline"
                                   target="_blank">
                                    {{ __('Edit Template') }}
                                </a>
                            </template>
                        </p>
                    </div>
                </div>
            </x-card>

            <div x-data="{ openEmailSenderSettings: false }" class="mt-6">
                <x-card>
                    <x-slot name="header">
                        {{ __('Email Sender Settings') }}
                    </x-slot>
                    <x-slot name="headerRight">
                        <button type="button" @click="openEmailSenderSettings = !openEmailSenderSettings" class="btn-default">
                            <iconify-icon :icon="openEmailSenderSettings ? 'lucide:chevron-up' : 'lucide:chevron-down'" class="w-4 h-4 mr-1 inline-block"></iconify-icon>
                            <span x-text="openEmailSenderSettings ? '{{ __('Hide Settings') }}' : '{{ __('Show Settings') }}'"></span>
                        </button>
                    </x-slot>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5" x-show="openEmailSenderSettings" x-cloak>
                        <div>
                            <label for="from_email" class="form-label">{{ __('From Email') }}</label>
                            <input type="email" id="from_email" name="from_email" class="form-control @error('from_email') border-red-500 @enderror" value="{{ old('from_email', $notification->from_email ?? '') }}" placeholder="{{ __('Leave empty to use default') }}">
                            <p class="text-xs text-gray-500 mt-1.5">{{ __('Override default from email') }}</p>
                        </div>

                        <div>
                            <label for="from_name" class="form-label">{{ __('From Name') }}</label>
                            <input type="text" id="from_name" name="from_name" class="form-control @error('from_name') border-red-500 @enderror" value="{{ old('from_name', $notification->from_name ?? '') }}" placeholder="{{ __('Leave empty to use default') }}">
                            <p class="text-xs text-gray-500 mt-1.5">{{ __('Override default from name') }}</p>
                        </div>

                        <div>
                            <label for="reply_to_email" class="form-label">{{ __('Reply-To Email') }}</label>
                            <input type="email" id="reply_to_email" name="reply_to_email" class="form-control @error('reply_to_email') border-red-500 @enderror" value="{{ old('reply_to_email', $notification->reply_to_email ?? '') }}" placeholder="{{ __('Leave empty to use default') }}">
                            <p class="text-xs text-gray-500 mt-1.5">{{ __('Override default reply-to email') }}</p>
                        </div>

                        <div>
                            <label for="reply_to_name" class="form-label">{{ __('Reply-To Name') }}</label>
                            <input type="text" id="reply_to_name" name="reply_to_name" class="form-control @error('reply_to_name') border-red-500 @enderror" value="{{ old('reply_to_name', $notification->reply_to_name ?? '') }}" placeholder="{{ __('Leave empty to use default') }}">
                            <p class="text-xs text-gray-500 mt-1.5">{{ __('Override default reply-to name') }}</p>
                        </div>
                    </div>

                    <div x-show="!openEmailSenderSettings" x-cloak class="text-sm text-gray-500 mt-2">
                        {{ __('Use default email sender settings unless overridden here.') }}
                    </div>
                </x-card>
            </div>
        </div>
    </div>
</form>

<script>
function notificationForm() {
    return {
        selectedTemplateId: '{{ old('email_template_id', $notification->email_template_id ?? '') }}',
        loading: false,
        templateData: {
            name: '',
            subject: '',
            body_html: ''
        },

        init() {
            // Listen for combobox changes
            document.addEventListener('combobox-change', (event) => {
                if (event.detail.name === 'receiver_type') {
                    this.toggleReceiverFields(event.detail.value);
                }
                if (event.detail.name === 'email_template_id') {
                    this.selectedTemplateId = event.detail.value;
                    this.loadTemplatePreview(event.detail.value);
                }
            });

            // Initialize receiver fields
            const receiverTypeSelect = document.querySelector('select[name="receiver_type"]');
            if (receiverTypeSelect) {
                receiverTypeSelect.addEventListener('change', (e) => {
                    this.toggleReceiverFields(e.target.value);
                });
                this.toggleReceiverFields(receiverTypeSelect.value);
            }

            // Initialize template select
            const templateSelect = document.querySelector('select[name="email_template_id"]');
            if (templateSelect) {
                templateSelect.addEventListener('change', (e) => {
                    this.selectedTemplateId = e.target.value;
                    this.loadTemplatePreview(e.target.value);
                });
            }

            // Load initial template preview if template is already selected
            if (this.selectedTemplateId) {
                this.loadTemplatePreview(this.selectedTemplateId);
            }
        },

        toggleReceiverFields(receiverType) {
            const receiverIdsField = document.getElementById('receiver_ids_field');
            const receiverEmailsField = document.getElementById('receiver_emails_field');

            receiverIdsField.classList.add('hidden');
            receiverEmailsField.classList.add('hidden');

            if (receiverType === 'contact' || receiverType === 'user') {
                receiverIdsField.classList.remove('hidden');
            } else if (receiverType === 'any_email') {
                receiverEmailsField.classList.remove('hidden');
            }
        },

        async loadTemplatePreview(templateId) {
            if (!templateId || templateId === '') {
                this.templateData = { name: '', subject: '', body_html: '' };
                return;
            }

            this.loading = true;

            try {
                const url = `/admin/settings/email-templates/${templateId}/content`;
                const response = await fetch(url, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin'
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();

                this.templateData = {
                    name: data.name || '',
                    subject: data.subject || '',
                    body_html: data.body_html || ''
                };

                // Set loading to false first so the iframe becomes visible
                this.loading = false;

                // Wait for DOM to update, then write to iframe
                await this.$nextTick();

                // Use setTimeout to ensure the iframe is fully rendered
                setTimeout(() => {
                    this.updateIframeContent();
                }, 100);

            } catch (error) {
                console.error('Error loading template preview:', error);
                this.templateData = { name: '', subject: '', body_html: '' };
                this.loading = false;
            }
        },

        updateIframeContent() {
            const iframe = document.getElementById('template-preview-iframe');
            if (!iframe || !this.templateData.body_html) {
                return;
            }

            try {
                const doc = iframe.contentDocument || iframe.contentWindow.document;
                doc.open();
                doc.write(this.templateData.body_html);
                doc.close();

                // Adjust iframe height based on content after it loads
                setTimeout(() => {
                    try {
                        const height = doc.body ? doc.body.scrollHeight : 400;
                        iframe.style.height = Math.min(Math.max(height + 20, 400), 600) + 'px';
                    } catch (e) {
                        // Cross-origin restriction, use default height
                        iframe.style.height = '400px';
                    }
                }, 200);
            } catch (e) {
                console.error('Error updating iframe:', e);
            }
        }
    };
}
</script>
