@props([
    'editorId' => 'editor',
    'height' => '200px',
    'maxHeight' => '500px',
    'type' => 'full', // Options: 'full', 'basic', 'minimal'
    'customToolbar' => null, // For custom toolbar configuration
])

@once
<link rel="stylesheet" href="{{ asset('vendor/quill/quill.min.css') }}" />
<style>
    .ql-editor {
        min-height: 200px;
        max-height: 500px;
        overflow-y: auto;
    }
    .ql-toolbar.ql-snow {
        border-radius: 10px 10px 0px 0px;
    }
    .ql-container {
        min-height: 200px;
    }
    /* Create a container for Quill to target */
    .quill-container {
        border: 1px solid #ccc;
        border-radius: 0 0 10px 10px;
        background: transparent;
    }
    .dark .quill-container {
        border-color: #4b5563;
        color: #e5e7eb;
    }
    .dark .ql-snow {
        border-color: #4b5563;
    }
    .dark .ql-toolbar.ql-snow .ql-picker-label,
    .dark .ql-toolbar.ql-snow .ql-picker-options,
    .dark .ql-toolbar.ql-snow button,
    .dark .ql-toolbar.ql-snow span {
        color: #e5e7eb;
    }
    .dark .ql-snow .ql-stroke {
        stroke: #e5e7eb;
    }
    .dark .ql-snow .ql-fill {
        fill: #e5e7eb;
    }
    .dark .ql-editor.ql-blank::before {
        color: rgba(255, 255, 255, 0.6);
    }
</style>

<script src="{{ asset('vendor/quill/quill.min.js') }}"></script>
@endonce

<script data-navigate-once>
    document.addEventListener('livewire:navigated', function() {
        const editorId = '{{ $editorId }}';
        const editorType = '{{ $type }}';
        const textareaElement = document.getElementById(editorId);
        const customToolbar = @json($customToolbar);

        if (!textareaElement) {
            console.error(`Textarea with ID "${editorId}" not found`);
            return;
        }

        // Create a div after the textarea to host Quill
        const quillContainer = document.createElement('div');
        quillContainer.id = `quill-${editorId}`;
        quillContainer.className = 'quill-container';
        textareaElement.insertAdjacentElement('afterend', quillContainer);

        // Store original textarea content
        const initialContent = textareaElement.value || '';

        // Define toolbar configurations based on type
        const toolbarConfigs = {
            full: [
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                ['blockquote'],
                [{ 'align': [] }],
                [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                [{ 'indent': '-1' }, { 'indent': '+1' }],
                [{ 'color': [] }, { 'background': [] }],
                [{ 'font': [] }],
                ['link', 'image', 'video', 'code-block']
            ],
            basic: [
                ['bold', 'italic', 'underline'],
                [{ 'header': [1, 2, 3, false] }],
                [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                ['link']
            ],
            minimal: [
                ['bold', 'italic'],
                [{ 'list': 'ordered' }, { 'list': 'bullet' }]
            ]
        };

        // Select toolbar configuration based on type or use custom if provided
        const toolbarConfig = customToolbar ? JSON.parse(customToolbar) :
                             (toolbarConfigs[editorType] || toolbarConfigs.basic);

        // Initialize Quill on the container div
        const quill = new Quill(`#quill-${editorId}`, {
            theme: "snow",
            placeholder: '{{ __('Type here...') }}',
            modules: {
                toolbar: toolbarConfig
            }
        });

        window['quill_' + editorId] = quill;

        // Set initial content from textarea
        if (initialContent) {
            quill.clipboard.dangerouslyPasteHTML(initialContent);
        }

        // Hide textarea visually but keep it in the DOM for form submission
        textareaElement.style.display = 'none';

        // Update textarea on editor change for form submission
        quill.on('text-change', function() {
            textareaElement.value = quill.root.innerHTML;
            
            // Trigger form change detection for the unsaved changes warning
            const event = new Event('input', { bubbles: true });
            textareaElement.dispatchEvent(event);
        });

        // Also update on form submit to ensure the latest content is captured
        const form = textareaElement.closest('form');
        if (form) {
            form.addEventListener('submit', function() {
                textareaElement.value = quill.root.innerHTML;
            });
        }
    });
</script>
