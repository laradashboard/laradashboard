<form method="POST" action="{{ isset($template) ? route('admin.email-templates.update', $template->id) : route('admin.email-templates.store') }}" enctype="multipart/form-data">
    @csrf
    @if (isset($template))
        @method('PUT')
    @endif

    @if ($errors->any())
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-md">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">{{ __('There were errors with your submission:') }}</h3>
                    <div class="mt-2 text-sm text-red-700">
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
                <x-inputs.combobox label="{{ __('Template Type') }}" name="type" :options="$templateTypes ?? []"
                    placeholder="{{ __('Select Template Type') }}" selected="{{ old('type', $selectedType ?? '') }}"
                    required />
                @error('type')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror

                @php
                    $headerOptions = ['' => __('No Header')];
                    if (isset($template)) {
                        foreach ($headerTemplates ?? [] as $headerTemplate) {
                            $headerOptions[$headerTemplate->id] = $headerTemplate->name;
                        }
                    } else {
                        foreach ($availableTemplates ?? [] as $availableTemplate) {
                            $headerOptions[$availableTemplate->id] = $availableTemplate->name;
                        }
                    }
                @endphp
                <x-inputs.combobox label="{{ __('Header Template') }}" name="header_template_id" :options="$headerOptions"
                    placeholder="{{ __('Select Header Template') }}"
                    selected="{{ old('header_template_id', $template->header_template_id ?? '') }}" />
                @error('header_template_id')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror

                @php
                    $footerOptions = ['' => __('No Footer')];
                    if (isset($template)) {
                        foreach ($footerTemplates ?? [] as $footerTemplate) {
                            $footerOptions[$footerTemplate->id] = $footerTemplate->name;
                        }
                    } else {
                        foreach ($availableTemplates ?? [] as $availableTemplate) {
                            $footerOptions[$availableTemplate->id] = $availableTemplate->name;
                        }
                    }
                @endphp
                <x-inputs.combobox label="{{ __('Footer Template') }}" name="footer_template_id" :options="$footerOptions"
                    placeholder="{{ __('Select Footer Template') }}"
                    selected="{{ old('footer_template_id', $template->footer_template_id ?? '') }}" />
                @error('footer_template_id')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror

                <div class="pt-1">
                    <label class="flex items-center justify-between cursor-pointer group">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Active Status') }}</span>
                        <div>
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" id="is_active" name="is_active" value="1" class="sr-only peer" {{ old('is_active', $template->is_active ?? true) ? 'checked' : '' }}>
                            <div class="relative w-11 h-6 bg-gray-200 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary"></div>
                        </div>
                    </label>
                </div>

                <div class="flex flex-col gap-3">
                    <button type="submit" class="btn btn-primary w-full justify-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        {{ isset($template) ? __('Update Template') : __('Create Template') }}
                    </button>
                    @if (isset($template))
                        <a href="{{ route('admin.email-templates.preview-page', $template->id) }}" target="_blank" class="btn btn-secondary w-full justify-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            {{ __('Preview Template') }}
                        </a>
                    @endif
                    <a href="{{ route('admin.email-templates.index') }}" class="btn btn-secondary w-full justify-center">
                        {{ __('Cancel') }}
                    </a>
                </div>
            </x-card>
        </div>

        <div class="flex-1 min-w-0">
            <x-card>
                <div class="space-y-5">
                    <div>
                        <label for="template_name" class="form-label">
                            {{ __('Template Name') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="template_name" name="name" class="form-control @error('name') border-red-500 @enderror" value="{{ old('name', $template->name ?? '') }}" placeholder="{{ __('e.g., Welcome Email, Newsletter Template') }}" required>
                        @error('name')
                            <p class="text-xs text-red-600 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <div id="description-field">
                        <label for="template_description" class="form-label">{{ __('Description') }}</label>
                        <textarea id="template_description" name="description" rows="2" class="form-control @error('description') border-red-500 @enderror" placeholder="{{ __('Brief description of when to use this template...') }}">{{ old('description', $template->description ?? '') }}</textarea>
                        @error('description')
                            <p class="text-xs text-red-600 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    @if (!isset($template))
                        <div>
                            <label for="template_selector" class="form-label">{{ __('Load from Existing Template') }}</label>
                            <select id="template_selector" class="form-control" onchange="loadTemplateContent(this.value)">
                                <option value="">{{ __('Start from scratch or select a template...') }}</option>
                                @foreach ($availableTemplates ?? [] as $availableTemplate)
                                    <option value="{{ $availableTemplate->id }}">{{ $availableTemplate->name }}</option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-500 mt-1.5">{{ __('Copy content from an existing template as a starting point') }}</p>
                        </div>
                    @endif
                </div>

                <div id="subject-field">
                    <div class="flex items-center justify-between mb-2">
                        <label for="email_subject" class="form-label w-full">
                            {{ __('Email Subject') }} <span class="text-red-500">*</span>
                        </label>
                        <x-variable-selector target-id="email_subject" :variables="$templateVariables ?? []" label="Add Variable" />
                    </div>
                    <input type="text" id="email_subject" name="subject" class="form-control @error('subject') border-red-500 @enderror" value="{{ old('subject', $template->subject ?? '') }}" placeholder="{{ __('Your compelling email subject line...') }}" required>
                    <p class="text-xs text-gray-500 mt-1.5">{{ __('Use variables like {first_name}, {company}, etc. for personalization') }}</p>
                    @error('subject')
                        <p class="text-xs text-red-600 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2 w-full">
                        <label for="body_html" class="form-label w-full">{{ __('HTML Content') }}</label>
                        <x-variable-selector target-id="body_html" :variables="$templateVariables ?? []" label="Add Variable" />
                    </div>
                    <textarea name="body_html" id="body_html" rows="4" class="block w-full border-0 focus:ring-0 focus:outline-none" placeholder="{{ __('Compose your email content...') }}">{{ old('body_html', $template->body_html ?? '') }}</textarea>
                    @push('scripts')
                        <x-text-editor :minHeight="'400px'" :maxHeight="'1200px'" :editor-id="'body_html'" type="full" />
                    @endpush
                    @error('body_html')
                        <p class="text-xs text-red-600 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>
            </x-card>
        </div>
    </div>
</form>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Listen for combobox changes
    document.addEventListener('combobox-change', function(event) {
        if (event.detail.name === 'type') {
            toggleFieldsBasedOnType(event.detail.value);
        }
    });
    
    // Also listen for direct select changes
    const typeSelect = document.querySelector('select[name="type"]');
    if (typeSelect) {
        typeSelect.addEventListener('change', function() {
            toggleFieldsBasedOnType(this.value);
        });
        // Initial call
        toggleFieldsBasedOnType(typeSelect.value);
    }
});

function toggleFieldsBasedOnType(selectedType) {
    if (!selectedType) {
        const typeSelect = document.querySelector('select[name="type"]');
        selectedType = typeSelect ? typeSelect.value : '';
    }
    
    const subjectField = document.getElementById('subject-field');
    const descriptionField = document.getElementById('description-field');
    
    const shouldHide = (selectedType === 'header' || selectedType === 'footer');
    
    [subjectField, descriptionField].forEach(field => {
        if (field) {
            field.style.display = shouldHide ? 'none' : 'block';
            
            // Handle required attributes
            const requiredInputs = field.querySelectorAll('[required]');
            requiredInputs.forEach(input => {
                if (shouldHide) {
                    input.removeAttribute('required');
                    input.dataset.wasRequired = 'true';
                } else if (input.dataset.wasRequired) {
                    input.setAttribute('required', 'required');
                }
            });
        }
    });
}

function loadTemplateContent(templateId) {
    if (!templateId) return;
    
    fetch(`/admin/email-templates/${templateId}/content`)
        .then(response => response.json())
        .then(data => {
            if (data.subject) {
                document.getElementById('email_subject').value = data.subject;
            }
            if (data.body_html) {
                // For text editor, set the content
                const htmlEditor = document.getElementById('body_html');
                if (htmlEditor) {
                    htmlEditor.value = data.body_html;
                    // Trigger change event for text editor
                    htmlEditor.dispatchEvent(new Event('change'));
                }
            }
        })
        .catch(error => {
            console.error('Error loading template content:', error);
        });
}
</script>