<form method="POST"
    action="{{ isset($template) ? route('admin.email-templates.update', $template->uuid) : route('admin.email-templates.store') }}"
    enctype="multipart/form-data">
    @csrf
    @if (isset($template))
        @method('PUT')
    @endif

    <div class="container mx-auto px-4 mt-4">
        <div class="flex flex-col lg:flex-row w-full gap-4">
            <div class="w-full lg:w-3/12">
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                    <div class="mb-4">
                        <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('Template Type') }} <span class="text-red-500">*</span>
                        </label>
                        <select name="type" id="type" class="form-control" required>
                            <option value="">{{ __('Select Template Type') }}</option>
                            @foreach ($templateTypes ?? [] as $value => $label)
                                <option value="{{ $value }}"
                                    {{ old('type', $template->type->value ?? '') == $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('type')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Status') }}</label>
                        <div class="flex items-center gap-3">
                            <span class="text-sm text-gray-600 dark:text-gray-400">{{ __('Inactive') }}</span>
                            <label class="relative inline-flex cursor-pointer">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" id="is_active" name="is_active" value="1"
                                    class="sr-only"
                                    {{ old('is_active', $template->is_active ?? true) ? 'checked' : '' }}>
                                <div class="relative">
                                    <div class="block bg-gray-600 w-14 h-8 rounded-full"></div>
                                    <div class="dot absolute left-1 top-1 bg-white w-6 h-6 rounded-full transition"></div>
                                </div>
                            </label>
                            <span class="text-sm text-gray-600 dark:text-gray-400">{{ __('Active') }}</span>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 pt-6">
                        <a href="{{ route('admin.email-templates.index') }}" class="btn btn-secondary">
                            {{ __('Cancel') }}
                        </a>
                        <button type="submit" class="btn btn-primary">
                            {{ isset($template) ? __('Update') : __('Create') }}
                        </button>
                    </div>
                </div>
            </div>

            <div class="w-full lg:w-9/12">
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                    <!-- Basic Information -->
                    <div class="mb-6">
                        <h3 class="font-medium text-lg text-gray-800 dark:text-gray-200 mb-4">
                            {{ __('Basic Information') }}
                        </h3>

                        <div class="grid grid-cols-1 gap-6">
                            <div>
                                <label for="template_name"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    {{ __('Template Name') }} <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="template_name" name="name"
                                    class="form-control @error('name') border-red-500 @enderror"
                                    value="{{ old('name', $template->name ?? '') }}"
                                    placeholder="{{ __('Enter template name...') }}" required>
                                @error('name')
                                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="template_description"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    {{ __('Description') }}
                                </label>
                                <textarea id="template_description" name="description" rows="3"
                                    class="form-control @error('description') border-red-500 @enderror"
                                    placeholder="{{ __('Optional description for this template...') }}">{{ old('description', $template->description ?? '') }}</textarea>
                                @error('description')
                                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Email Content -->
                    <div class="mb-6">
                        <h3 class="font-medium text-lg text-gray-800 dark:text-gray-200 mb-4">
                            {{ __('Email Content') }}
                        </h3>

                        <div class="grid grid-cols-1 gap-6">
                            <div>
                                @php
                                    $variables = [
                                        ['label' => 'First Name', 'value' => '{{ first_name }}'],
                                        ['label' => 'Last Name', 'value' => '{{ last_name }}'],
                                        ['label' => 'Company', 'value' => '{{ company }}'],
                                        ['label' => 'Email', 'value' => '{{ email }}'],
                                        ['label' => 'Activity Title', 'value' => '{{ activity_title }}'],
                                        [
                                            'label' => 'Activity Description',
                                            'value' => '{{ activity_description }}',
                                        ],
                                        ['label' => 'Due Date', 'value' => '{{ due_date }}'],
                                    ];
                                @endphp
                                <label for="email_subject"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    {{ __('Email Subject') }} <span class="text-red-500">*</span>
                                    <x-variable-selector target-id="email_subject" :variables="$variables"
                                        label="Add Variable" />
                                </label>
                                <div class="flex items-center">
                                    <input type="text" id="email_subject" name="subject"
                                        class="form-control @error('subject') border-red-500 @enderror"
                                        value="{{ old('subject', $template->subject ?? '') }}"
                                        placeholder="{{ __('Enter email subject line...') }}" required>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">
                                    {{ __('You can use variables like {first_name}, {last_name}, {company}, etc.') }}
                                </p>
                                @error('subject')
                                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            @if (!isset($template))
                                <div class="mb-4">
                                    <label for="template_selector"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        {{ __('Load from Template') }}
                                    </label>
                                    <select id="template_selector" class="form-control"
                                        onchange="loadTemplateContent(this.value)">
                                        <option value="">{{ __('Select a template to load content...') }}
                                        </option>
                                        @foreach ($availableTemplates ?? [] as $availableTemplate)
                                            <option value="{{ $availableTemplate->id }}">
                                                {{ $availableTemplate->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <label for="body_html"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        {{ __('HTML Content') }}
                                        <x-variable-selector target-id="body_html" :variables="$variables"
                                            label="Add Variable" />
                                    </label>
                                </div>
                                <div class="overflow-hidden border border-gray-300 dark:border-gray-600 rounded-md">
                                    <textarea name="body_html" id="body_html" rows="4" class="block w-full border-0 focus:ring-0 focus:outline-none"
                                        placeholder="{{ __('Email body content...') }}">{{ old('body_html', $template->body_html ?? '') }}</textarea>
                                </div>
                                @push('scripts')
                                    <x-quill-editor :height="'200px'" :editor-id="'body_html'" type="basic" />
                                @endpush
                                @error('body_html')
                                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <label for="body_text"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        {{ __('Plain Text Content') }}
                                        <x-variable-selector target-id="body_text" :variables="$variables"
                                            label="Add Variable" />
                                    </label>
                                </div>
                                <textarea id="body_text" name="body_text" rows="15"
                                    class="form-control @error('body_text') border-red-500 @enderror"
                                    placeholder="{{ __('Enter plain text version of your email...') }}">{{ old('body_text', $template->body_text ?? '') }}</textarea>
                                <p class="text-xs text-gray-500 mt-1">
                                    {{ __('Plain text fallback for email clients that don\'t support HTML.') }}
                                </p>
                                @error('body_text')
                                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

@push('styles')
<style>
.toggle-switch input:checked + div .dot {
    transform: translateX(100%);
    background-color: #48bb78;
}
.toggle-switch input:checked + div {
    background-color: #48bb78;
}
</style>
@endpush

@push('scripts')
    <script>
        // Toggle switch functionality
        document.addEventListener('DOMContentLoaded', function() {
            const toggle = document.getElementById('is_active');
            const toggleContainer = toggle.nextElementSibling;
            const dot = toggleContainer.querySelector('.dot');
            
            function updateToggle() {
                if (toggle.checked) {
                    toggleContainer.firstElementChild.classList.remove('bg-gray-600');
                    toggleContainer.firstElementChild.classList.add('bg-green-500');
                    dot.style.transform = 'translateX(24px)';
                } else {
                    toggleContainer.firstElementChild.classList.remove('bg-green-500');
                    toggleContainer.firstElementChild.classList.add('bg-gray-600');
                    dot.style.transform = 'translateX(0px)';
                }
            }
            
            // Initial state
            updateToggle();
            
            // Toggle on click
            toggle.addEventListener('change', updateToggle);
        });
        
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
