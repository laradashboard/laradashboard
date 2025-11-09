<form method="POST"
    action="{{ isset($template) ? route('admin.email-templates.update', $template->uuid) : route('admin.email-templates.store') }}"
    enctype="multipart/form-data">
    @csrf
    @if (isset($template))
        @method('PUT')
    @endif

    <div class="container mx-auto px-4">
        <div class="flex flex-col lg:flex-row gap-6">
            <div class="w-full lg:w-80 lg:flex-shrink-0 space-y-6">
                <x-card class="sticky top-24">
                    <x-inputs.combobox
                        label="{{ __('Template Type') }}"
                        name="type"
                        :options="$templateTypes ?? []"
                        placeholder="{{ __('Select Template Type') }}"
                        selected="{{ old('type', $selectedType ?? '') }}"
                        required
                    />
                    @error('type')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror

                    @php
                        $headerOptions = ['' => __('No Header')];
                        if(isset($template)) {
                            foreach($headerTemplates ?? [] as $headerTemplate) {
                                $headerOptions[$headerTemplate->id] = $headerTemplate->name;
                            }
                        } else {
                            foreach($availableTemplates ?? [] as $availableTemplate) {
                                $headerOptions[$availableTemplate->id] = $availableTemplate->name;
                            }
                        }
                    @endphp
                    <x-inputs.combobox
                        label="{{ __('Header Template') }}"
                        name="header_template_id"
                        :options="$headerOptions"
                        placeholder="{{ __('Select Header Template') }}"
                        selected="{{ old('header_template_id', $template->header_template_id ?? '') }}" />
                    @error('header_template_id')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror

                    @php
                        $footerOptions = ['' => __('No Footer')];
                        if(isset($template)) {
                            foreach($footerTemplates ?? [] as $footerTemplate) {
                                $footerOptions[$footerTemplate->id] = $footerTemplate->name;
                            }
                        } else {
                            foreach($availableTemplates ?? [] as $availableTemplate) {
                                $footerOptions[$availableTemplate->id] = $availableTemplate->name;
                            }
                        }
                    @endphp
                    <x-inputs.combobox
                        label="{{ __('Footer Template') }}"
                        name="footer_template_id"
                        :options="$footerOptions"
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
                                <input type="checkbox" id="is_active" name="is_active" value="1"
                                    class="sr-only peer"
                                    {{ old('is_active', $template->is_active ?? true) ? 'checked' : '' }}>
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
                            <input type="text" id="template_name" name="name"
                                class="form-control @error('name') border-red-500 @enderror"
                                value="{{ old('name', $template->name ?? '') }}"
                                placeholder="{{ __('e.g., Welcome Email, Newsletter Template') }}" required>
                            @error('name')
                                <p class="text-xs text-red-600 mt-1.5">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="template_description" class="form-label">
                                {{ __('Description') }}
                            </label>
                            <textarea id="template_description" name="description" rows="2"
                                class="form-control @error('description') border-red-500 @enderror"
                                placeholder="{{ __('Brief description of when to use this template...') }}">{{ old('description', $template->description ?? '') }}</textarea>
                            @error('description')
                                <p class="text-xs text-red-600 mt-1.5">{{ $message }}</p>
                            @enderror
                        </div>

                        @if (!isset($template))
                            <div>
                                <label for="template_selector" class="form-label">
                                    {{ __('Load from Existing Template') }}
                                </label>
                                <select id="template_selector" class="form-control"
                                    onchange="loadTemplateContent(this.value)">
                                    <option value="">{{ __('Start from scratch or select a template...') }}</option>
                                    @foreach ($availableTemplates ?? [] as $availableTemplate)
                                        <option value="{{ $availableTemplate->id }}">{{ $availableTemplate->name }}</option>
                                    @endforeach
                                </select>
                                <p class="text-xs text-gray-500 mt-1.5">{{ __('Copy content from an existing template as a starting point') }}</p>
                            </div>
                            <div class="border-t border-gray-200 dark:border-gray-700"></div>
                        @endif
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label for="email_subject" class="form-label w-full">
                                {{ __('Email Subject') }} <span class="text-red-500">*</span>
                            </label>
                            <x-variable-selector target-id="email_subject" :variables="$templateVariables ?? []" label="Add Variable" />
                        </div>
                        <input type="text" id="email_subject" name="subject"
                            class="form-control @error('subject') border-red-500 @enderror"
                            value="{{ old('subject', $template->subject ?? '') }}"
                            placeholder="{{ __('Your compelling email subject line...') }}" required>
                        <p class="text-xs text-gray-500 mt-1.5">
                            {{ __('Use variables like {first_name}, {company}, etc. for personalization') }}
                        </p>
                        @error('subject')
                            <p class="text-xs text-red-600 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-2 w-full">
                            <label for="body_html" class="form-label w-full">
                                {{ __('HTML Content') }}
                            </label>
                            <x-variable-selector target-id="body_html" :variables="$templateVariables ?? []" label="Add Variable" />
                        </div>

                        <textarea name="body_html" id="body_html" rows="4"
                            class="block w-full border-0 focus:ring-0 focus:outline-none"
                            placeholder="{{ __('Compose your email content...') }}">{{ old('body_html', $template->body_html ?? '') }}</textarea>
                        @push('scripts')
                            <x-quill-editor :height="'200px'" :editor-id="'body_html'" type="full" />
                        @endpush
                        @error('body_html')
                            <p class="text-xs text-red-600 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-2 w-full">
                            <label for="body_text" class="form-label w-full">
                                {{ __('Plain Text Version') }}
                            </label>
                            <x-variable-selector target-id="body_text" :variables="$templateVariables ?? []" label="Add Variable" />
                        </div>
                        <textarea id="body_text" name="body_text" rows="12"
                            class="form-control !h-auto @error('body_text') border-red-500 @enderror font-mono text-sm"
                            placeholder="{{ __('Plain text version for email clients that don\'t support HTML...') }}">{{ old('body_text', $template->body_text ?? '') }}</textarea>
                        <p class="text-xs text-gray-500 mt-1.5">
                            {{ __('Fallback version for email clients without HTML support') }}
                        </p>
                        @error('body_text')
                            <p class="text-xs text-red-600 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>
                </x-card>
            </div>
        </div>
    </div>
</form>

@push('scripts')
    <script>
        async function loadTemplateContent(templateId) {
            if (!templateId) return;

            try {
                const response = await fetch(`/admin/email-templates/${templateId}/content`);
                const data = await response.json();

                if (response.ok) {
                    // Update subject field
                    const subjectInput = document.getElementById('email_subject');
                    if (subjectInput && data.subject) {
                        subjectInput.value = data.subject;
                    }

                    // Update HTML content textarea
                    const htmlTextarea = document.getElementById('body_html');
                    if (htmlTextarea) {
                        htmlTextarea.value = data.body_html || '';
                    }

                    // Update Quill editor content
                    setTimeout(() => {
                        const quill = window['quill-body_html'];
                        if (quill && data.body_html) {
                            quill.clipboard.dangerouslyPasteHTML(data.body_html);
                        }
                    }, 100);

                    // Update plain text content
                    const textTextarea = document.getElementById('body_text');
                    if (textTextarea && data.body_text) {
                        textTextarea.value = data.body_text;
                    }
                } else {
                    console.error('Failed to load template content:', data.error);
                }
            } catch (error) {
                console.error('Error loading template content:', error);
            }
        }
    </script>
@endpush
