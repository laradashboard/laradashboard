<form method="POST" action="{{ isset($notification) ? route('admin.notifications.update', $notification->id) : route('admin.notifications.store') }}">
    @csrf
    @if (isset($notification))
        @method('PUT')
    @endif

    @if ($errors->any())
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-md dark:bg-red-900/20 dark:border-red-800">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800 dark:text-red-300">{{ __('There were errors with your submission:') }}</h3>
                    <div class="mt-2 text-sm text-red-700 dark:text-red-400">
                        <ul class="list-disc pl-5 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="flex flex-col lg:flex-row gap-6">
        <div class="w-full lg:w-80 lg:flex-shrink-0 space-y-6">
            <x-card class="sticky top-24">
                <x-inputs.combobox label="{{ __('Notification Type') }}" name="notification_type" :options="$notificationTypes ?? []"
                    placeholder="{{ __('Select Notification Type') }}" selected="{{ old('notification_type', $notification->notification_type->value ?? '') }}"
                    required />
                @error('notification_type')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror

                <x-inputs.combobox label="{{ __('Receiver Type') }}" name="receiver_type" :options="$receiverTypes ?? []"
                    placeholder="{{ __('Select Receiver Type') }}" selected="{{ old('receiver_type', $notification->receiver_type->value ?? '') }}"
                    required />
                @error('receiver_type')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror

                <x-inputs.combobox label="{{ __('Email Template (Optional)') }}" name="email_template_id" :options="array_merge(['' => __('None - Use Custom Content')], $emailTemplates->pluck('name', 'id')->toArray())"
                    placeholder="{{ __('Select Email Template') }}" selected="{{ old('email_template_id', $notification->email_template_id ?? '') }}" />
                @error('email_template_id')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
                <p class="text-xs text-gray-500 mt-1">{{ __('Select a template or leave empty to use custom content below') }}</p>

                <div class="pt-2 border-t border-gray-200 dark:border-gray-700">
                    <label class="flex items-center justify-between cursor-pointer group">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Active Status') }}</span>
                        <div>
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" id="is_active" name="is_active" value="1" class="sr-only peer" {{ old('is_active', $notification->is_active ?? true) ? 'checked' : '' }}>
                            <div class="relative w-11 h-6 bg-gray-200 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary"></div>
                        </div>
                    </label>
                </div>

                <div class="pt-1">
                    <label class="flex items-center justify-between cursor-pointer group">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Track Opens') }}</span>
                        <div>
                            <input type="hidden" name="track_opens" value="0">
                            <input type="checkbox" id="track_opens" name="track_opens" value="1" class="sr-only peer" {{ old('track_opens', $notification->track_opens ?? true) ? 'checked' : '' }}>
                            <div class="relative w-11 h-6 bg-gray-200 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary"></div>
                        </div>
                    </label>
                </div>

                <div class="pt-1 pb-4">
                    <label class="flex items-center justify-between cursor-pointer group">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Track Clicks') }}</span>
                        <div>
                            <input type="hidden" name="track_clicks" value="0">
                            <input type="checkbox" id="track_clicks" name="track_clicks" value="1" class="sr-only peer" {{ old('track_clicks', $notification->track_clicks ?? true) ? 'checked' : '' }}>
                            <div class="relative w-11 h-6 bg-gray-200 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary"></div>
                        </div>
                    </label>
                </div>

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
                    <div>
                        <label for="notification_name" class="form-label">
                            {{ __('Notification Name') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="notification_name" name="name" class="form-control @error('name') border-red-500 @enderror" value="{{ old('name', $notification->name ?? '') }}" placeholder="{{ __('e.g., Forgot Password Notification, Welcome Email') }}" required>
                        @error('name')
                            <p class="text-xs text-red-600 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="notification_description" class="form-label">{{ __('Description') }}</label>
                        <textarea id="notification_description" name="description" rows="2" class="form-control @error('description') border-red-500 @enderror" placeholder="{{ __('Brief description of this notification...') }}">{{ old('description', $notification->description ?? '') }}</textarea>
                        @error('description')
                            <p class="text-xs text-red-600 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="border-t border-gray-200 dark:border-gray-700 pt-5">
                    <h4 class="text-base font-medium text-gray-900 dark:text-white mb-4">
                        {{ __('Email Content') }}
                    </h4>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        {{ __('You can either select an email template above or write custom content below. Custom content will override the template.') }}
                    </p>

                    <div>
                        <div class="flex items-center justify-between mb-2 w-full">
                            <label for="body_html" class="form-label">{{ __('HTML Content') }}</label>
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
                        @error('body_html')
                            <p class="text-xs text-red-600 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="border-t border-gray-200 dark:border-gray-700 pt-5">
                    <h4 class="text-base font-medium text-gray-900 dark:text-white mb-4">
                        {{ __('Email Sender Settings') }}
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label for="from_email" class="form-label">{{ __('From Email') }}</label>
                            <input type="email" id="from_email" name="from_email" class="form-control @error('from_email') border-red-500 @enderror" value="{{ old('from_email', $notification->from_email ?? '') }}" placeholder="{{ __('Leave empty to use default') }}">
                            <p class="text-xs text-gray-500 mt-1.5">{{ __('Override default from email') }}</p>
                            @error('from_email')
                                <p class="text-xs text-red-600 mt-1.5">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="from_name" class="form-label">{{ __('From Name') }}</label>
                            <input type="text" id="from_name" name="from_name" class="form-control @error('from_name') border-red-500 @enderror" value="{{ old('from_name', $notification->from_name ?? '') }}" placeholder="{{ __('Leave empty to use default') }}">
                            <p class="text-xs text-gray-500 mt-1.5">{{ __('Override default from name') }}</p>
                            @error('from_name')
                                <p class="text-xs text-red-600 mt-1.5">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="reply_to_email" class="form-label">{{ __('Reply-To Email') }}</label>
                            <input type="email" id="reply_to_email" name="reply_to_email" class="form-control @error('reply_to_email') border-red-500 @enderror" value="{{ old('reply_to_email', $notification->reply_to_email ?? '') }}" placeholder="{{ __('Leave empty to use default') }}">
                            <p class="text-xs text-gray-500 mt-1.5">{{ __('Override default reply-to email') }}</p>
                            @error('reply_to_email')
                                <p class="text-xs text-red-600 mt-1.5">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="reply_to_name" class="form-label">{{ __('Reply-To Name') }}</label>
                            <input type="text" id="reply_to_name" name="reply_to_name" class="form-control @error('reply_to_name') border-red-500 @enderror" value="{{ old('reply_to_name', $notification->reply_to_name ?? '') }}" placeholder="{{ __('Leave empty to use default') }}">
                            <p class="text-xs text-gray-500 mt-1.5">{{ __('Override default reply-to name') }}</p>
                            @error('reply_to_name')
                                <p class="text-xs text-red-600 mt-1.5">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="border-t border-gray-200 dark:border-gray-700 pt-5" id="receiver-settings">
                    <h4 class="text-base font-medium text-gray-900 dark:text-white mb-4">
                        {{ __('Receiver Settings') }}
                    </h4>
                    
                    <div id="receiver_ids_field" class="hidden">
                        <label for="receiver_ids" class="form-label">{{ __('Receiver IDs') }}</label>
                        <input type="text" id="receiver_ids" name="receiver_ids_text" class="form-control @error('receiver_ids') border-red-500 @enderror" value="{{ old('receiver_ids_text', isset($notification) ? implode(',', $notification->receiver_ids ?? []) : '') }}" placeholder="{{ __('Enter comma-separated IDs') }}">
                        <p class="text-xs text-gray-500 mt-1.5">{{ __('Enter user/contact IDs separated by commas') }}</p>
                        @error('receiver_ids')
                            <p class="text-xs text-red-600 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <div id="receiver_emails_field" class="hidden">
                        <label for="receiver_emails" class="form-label">{{ __('Email Addresses') }}</label>
                        <textarea id="receiver_emails" name="receiver_emails_text" rows="3" class="form-control @error('receiver_emails') border-red-500 @enderror" placeholder="{{ __('Enter email addresses, one per line or comma-separated') }}">{{ old('receiver_emails_text', isset($notification) ? implode("\n", $notification->receiver_emails ?? []) : '') }}</textarea>
                        <p class="text-xs text-gray-500 mt-1.5">{{ __('Enter email addresses separated by commas or new lines') }}</p>
                        @error('receiver_emails')
                            <p class="text-xs text-red-600 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </x-card>
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
