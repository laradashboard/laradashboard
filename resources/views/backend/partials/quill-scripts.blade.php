@php
$includeToolbar = isset($includeToolbar) ? $includeToolbar : true;
$editorId = isset($editorId) ? $editorId : 'editor';
@endphp

<link rel="stylesheet" href="{{ asset('vendor/quill/quill.min.css') }}" />
<style>
    .ql-toolbar.ql-snow {
        border-radius: 10px 10px 0px 0px;
    }
    .ql-container {
        min-height: 200px;
    }
    /* Create a container for Quill to target */
    #quill-{{ $editorId }} {
        border: 1px solid #ccc;
        border-radius: 0 0 10px 10px;
        background: transparent;
    }
    .dark #quill-{{ $editorId }} {
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

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const editorId = '{{ $editorId }}';
        const textareaElement = document.getElementById(editorId);

        if (!textareaElement) {
            console.error(`Textarea with ID "${editorId}" not found`);
            return;
        }

        // Store original textarea content
        const initialContent = textareaElement.value || '';

        // Initialize Quill on the container div we created
        const quill = new Quill(`#quill-${editorId}`, {
            theme: "snow",
            placeholder: '{{ __('Type here...') }}',
            modules: {
                toolbar: [
                    ['bold', 'italic', 'underline', 'strike'],
                    // [{ 'header': 1 }, { 'header': 2 }],
                    [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                    ['blockquote'],
                    [{ 'align': [] }],
                    [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                    // [{ 'script': 'sub' }, { 'script': 'super' }],
                    [{ 'indent': '-1' }, { 'indent': '+1' }],
                    // [{ 'direction': 'rtl' }],
                    // [{ 'size': ['small', false, 'large', 'huge'] }],
                    [{ 'color': [] }, { 'background': [] }],
                    [{ 'font': [] }],
                    // ['clean'],
                    ['link', 'image', 'video','code-block']
                ]
            }
        });

        // Set initial content from textarea
        if (initialContent) {
            quill.clipboard.dangerouslyPasteHTML(initialContent);
            console.log('Content loaded into editor:', initialContent);
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
