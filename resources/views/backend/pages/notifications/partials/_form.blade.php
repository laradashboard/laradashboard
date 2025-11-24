<form
    method="POST"
    action="{{ isset($notification) ? route('admin.notifications.update', $notification->id) : route('admin.notifications.store') }}"
    data-prevent-unsaved-changes
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
                    label="{{ __('Email Template (Optional)') }}"
                    name="email_template_id"
                    :options="$emailTemplates ?? []"
                    placeholder="{{ __('Select Email Template') }}"
                    selected="{{ old('email_template_id', $notification->email_template_id ?? '') }}"
                />



                <div class="flex flex-col gap-3">
                    <button type="submit" class="btn btn-primary w-full justify-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        {{ isset($notification) ? __('Update Notification') : __('Create Notification') }}
                    </button>
                    <a href="{{ route('admin.notifications.index') }}" class="btn btn-secondary w-full justify-center">
                        {{ __('Cancel') }}
                    </a>
                </div>
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
                        rows="2"
                        placeholder="{{ __('Brief description of this notification...') }}"
                    />

                    <div>
                        <div class="flex items-center justify-between mb-2 w-full">
                            <label class="form-label" html-for="body_html">{{ __('Notification Content') }}</label>
                            @if (isset($notification) && $notification->email_template_id)
                                <button type="button" onclick="loadTemplateContent({{ $notification->email_template_id }})" class="text-xs btn btn-secondary py-1 px-2">
                                    <svg class="w-3 h-3 mr-1 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                    </svg>
                                    {{ __('Load Template Content') }}
                                </button>
                            @endif
                        </div>
                        <textarea name="body_html" id="body_html" rows="4" class="block w-full border-0 focus:ring-0 focus:outline-none" placeholder="{{ __('Compose your email content...') }}">{{ old('body_html', $notification->body_html ?? '') }}</textarea>
                        @push('scripts')
                            <x-text-editor :minHeight="'400px'" :maxHeight="'1200px'" :editor-id="'body_html'" type="full" />
                        @endpush
                    </div>
                </div>
            </x-card>

            <x-card class="mt-6">
                <x-slot name="header">
                    {{ __('Receiver Settings') }}
                </x-slot>

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
                        <label for="receiver_ids" class="form-label">{{ __('Receiver IDs') }}</label>
                        <input type="text" id="receiver_ids" name="receiver_ids_text" class="form-control @error('receiver_ids') border-red-500 @enderror" value="{{ old('receiver_ids_text', isset($notification) ? implode(',', $notification->receiver_ids ?? []) : '') }}" placeholder="{{ __('Enter comma-separated IDs') }}">
                        <p class="text-xs text-gray-500 mt-1.5">{{ __('Enter user/contact IDs separated by commas') }}</p>
                    </div>

                    <div id="receiver_emails_field" class="hidden">
                        <label for="receiver_emails" class="form-label">{{ __('Email Addresses') }}</label>
                        <textarea id="receiver_emails" name="receiver_emails_text" rows="3" class="form-control @error('receiver_emails') border-red-500 @enderror" placeholder="{{ __('Enter email addresses, one per line or comma-separated') }}">{{ old('receiver_emails_text', isset($notification) ? implode("\n", $notification->receiver_emails ?? []) : '') }}</textarea>
                        <p class="text-xs text-gray-500 mt-1.5">{{ __('Enter email addresses separated by commas or new lines') }}</p>
                    </div>
                </div>
            </x-card>

            <div x-data="{ openEmailSenderSettings: false }" class="mt-6">
                <x-card class="mt-6">
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
document.addEventListener('DOMContentLoaded', function() {
    document.addEventListener('combobox-change', function(event) {
        if (event.detail.name === 'receiver_type') {
            toggleReceiverFields(event.detail.value);
        }
        if (event.detail.name === 'email_template_id') {
            loadTemplateContentFromCombobox(event.detail.value);
        }
    });
    
    const receiverTypeSelect = document.querySelector('select[name="receiver_type"]');
    if (receiverTypeSelect) {
        receiverTypeSelect.addEventListener('change', function() {
            toggleReceiverFields(this.value);
        });
        toggleReceiverFields(receiverTypeSelect.value);
    }

    // Direct select listener for email template
    const templateSelect = document.querySelector('select[name="email_template_id"]');
    if (templateSelect) {
        templateSelect.addEventListener('change', function() {
            loadTemplateContentFromCombobox(this.value);
        });
    }
});

function toggleReceiverFields(receiverType) {
    const receiverIdsField = document.getElementById('receiver_ids_field');
    const receiverEmailsField = document.getElementById('receiver_emails_field');
    
    receiverIdsField.classList.add('hidden');
    receiverEmailsField.classList.add('hidden');
    
    if (receiverType === 'contact' || receiverType === 'user') {
        receiverIdsField.classList.remove('hidden');
    } else if (receiverType === 'any_email') {
        receiverEmailsField.classList.remove('hidden');
    }
}

function loadTemplateContentFromCombobox(templateId) {
    if (!templateId || templateId === '') {
        return;
    }
    loadTemplateContent(templateId);
}

function loadTemplateContent(templateId) {
    if (!templateId) {
        return;
    }
    
    // Check if there's existing content in the editor
    let hasExistingContent = false;
    if (typeof tinymce !== 'undefined') {
        const editor = tinymce.get('body_html');
        if (editor) {
            const content = editor.getContent();
            hasExistingContent = content && content.trim().length > 0;
        }
    } else {
        const htmlEditor = document.getElementById('body_html');
        if (htmlEditor) {
            hasExistingContent = htmlEditor.value && htmlEditor.value.trim().length > 0;
        }
    }
    
    // Confirm before overwriting existing content
    if (hasExistingContent) {
        if (!confirm('This will replace the current content. Are you sure?')) {
            return;
        }
    }
    
    fetch(`/admin/settings/email-templates/${templateId}/content`)
        .then(response => response.json())
        .then(data => {
            // Load HTML content into TinyMCE editor
            if (data.body_html) {
                // Check if TinyMCE is initialized
                if (typeof tinymce !== 'undefined') {
                    const editor = tinymce.get('body_html');
                    if (editor) {
                        // Set content in TinyMCE
                        editor.setContent(data.body_html);
                        console.log('Template content loaded successfully into editor');
                    } else {
                        // Fallback: Set textarea value and wait for TinyMCE to initialize
                        const htmlEditor = document.getElementById('body_html');
                        if (htmlEditor) {
                            htmlEditor.value = data.body_html;
                            console.log('Template content loaded successfully into textarea');
                        }
                    }
                } else {
                    // TinyMCE not loaded yet, set textarea value
                    const htmlEditor = document.getElementById('body_html');
                    if (htmlEditor) {
                        htmlEditor.value = data.body_html;
                        console.log('Template content loaded successfully into textarea (TinyMCE not ready)');
                    }
                }
            }
        })
        .catch(error => {
            console.error('Error loading template content:', error);
            alert('Failed to load template content. Please try again.');
        });
}
</script>
