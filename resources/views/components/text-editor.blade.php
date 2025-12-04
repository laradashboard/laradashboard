@props([
    'editorId' => 'editor',
    'height' => '400px',
    'minHeight' => null, // If set, editor grows with content instead of fixed height
    'maxHeight' => '500px',
    'type' => 'full', // Options: 'full', 'basic', 'minimal'
    'customToolbar' => null, // For custom toolbar configuration
    'menubar' => false, // Show/hide menubar (File, Edit, View, etc.) - null = auto based on type, true = show, false = hide
])

@once
    <style>
        /* TinyMCE container styles */
        .tox-tinymce {
            border-radius: 10px !important;
            border: 1px solid var(--color-gray-200, #e5e7eb) !important;
        }

        /* Dark mode support */
        .dark .tox-tinymce {
            border-color: rgb(55 65 81) !important;
        }

        /* Toolbar styling */
        .tox .tox-toolbar {
            background: transparent !important;
        }

        /* Editor content area */
        .tox .tox-edit-area {
            border: none !important;
        }

        /* Remove unnecessary padding from editor body */
        .tox .tox-edit-area__iframe {
            border: none !important;
        }

        /* Focus state */
        .tox.tox-tinymce:focus-within {
            --tw-ring-offset-shadow: var(--tw-ring-inset) 0 0 0 0px var(--tw-ring-offset-color);
            --tw-ring-shadow: var(--tw-ring-inset) 0 0 0 calc(2px + 0px) var(--tw-ring-color);
            --tw-ring-color: rgb(var(--color-primary) / 1);
            box-shadow: var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow, 0 0 #0000);
        }

        /* Status bar */
        .tox .tox-statusbar {
            border-top: 1px solid var(--color-gray-200, #e5e7eb) !important;
        }

        .dark .tox .tox-statusbar {
            border-top-color: rgb(55 65 81) !important;
        }

        /* Hide upgrade/premium buttons and promotions */
        .tox-promotion,
        .tox-promotion-link,
        .tox .tox-statusbar__path,
        .tox .tox-statusbar a[href*="upgrade"],
        .tox .tox-statusbar a[href*="tiny.cloud"],
        .tox-statusbar__upgrade,
        button[title*="Upgrade"],
        a[title*="Upgrade"],
        .tox-promotion-container {
            display: none !important;
        }

        /* Hide help button if needed */
        button[title="Help"],
        .tox-tbtn[aria-label="Help"] {
            display: none !important;
        }
    </style>

    <!-- TinyMCE Self-Hosted (Open Source) -->
    <script src="{{ asset('vendor/tinymce/tinymce.min.js') }}"></script>
@endonce

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const editorId = '{{ $editorId }}';
        const editorType = '{{ $type }}';
        const customToolbar = @json($customToolbar);
        const menubarProp = @json($menubar);
        const textareaElement = document.getElementById(editorId);

        if (!textareaElement) {
            console.error(`Textarea with ID "${editorId}" not found`);
            return;
        }

        // Toolbar configurations based on type
        const toolbarConfigs = {
            full: customToolbar || 'undo redo | styles | bold italic underline strikethrough customMedia | alignleft aligncenter alignright alignjustify code | bullist numlist | outdent indent | forecolor backcolor | link image media table | codesample | blockquote hr | removeformat | fullscreen',
            basic: customToolbar || 'undo redo | bold italic underline customMedia | alignleft aligncenter alignright | bullist numlist | link image media | removeformat code',
            minimal: customToolbar || 'bold italic | bullist numlist'
        };

        const pluginsConfigs = {
            full: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount code fullscreen quickbars',
            basic: 'autolink link image lists wordcount',
            minimal: 'lists'
        };

        // Parse height values
        const minHeightValue = '{{ $minHeight }}';
        let heightValue = '{{ $height }}';
        const maxHeightValue = '{{ $maxHeight }}';
        
        const heightMatch = heightValue.match(/(\d+)px/);
        const numericHeight = heightMatch ? parseInt(heightMatch[1]) : 400;
        
        const maxHeightMatch = maxHeightValue.match(/(\d+)px/);
        const numericMaxHeight = maxHeightMatch ? parseInt(maxHeightMatch[1]) : 500;

        // Determine menubar visibility
        let showMenubar;
        if (menubarProp === null) {
            // Auto: show for full mode, hide for others
            showMenubar = editorType === 'full';
        } else {
            // Use explicit prop value
            showMenubar = menubarProp === true;
        }

        // Height configuration and plugins based on minHeight prop
        const heightConfig = {};
        let plugins = pluginsConfigs[editorType] || pluginsConfigs.basic;
        
        if (minHeightValue && minHeightValue !== '' && minHeightValue !== 'null') {
            // Use min_height for flexible growing editor - requires autoresize plugin
            const minHeightMatch = minHeightValue.match(/(\d+)px/);
            const numericMinHeight = minHeightMatch ? parseInt(minHeightMatch[1]) : 400;
            
            // Add autoresize plugin if not already present
            if (!plugins.includes('autoresize')) {
                plugins = 'autoresize ' + plugins;
            }
            
            // Configure autoresize
            heightConfig.min_height = numericMinHeight;
            heightConfig.max_height = numericMaxHeight;
            heightConfig.resize = false; // Disable manual resize handle
            heightConfig.autoresize_bottom_margin = 0;
            heightConfig.autoresize_overflow_padding = 0;
        } else {
            // Use fixed height (existing behavior)
            heightConfig.height = numericHeight;
        }

        // Initialize TinyMCE
        tinymce.init({
            selector: `#${editorId}`,
            ...heightConfig,
            menubar: showMenubar,
            plugins: plugins,
            toolbar: toolbarConfigs[editorType] || toolbarConfigs.basic,

            // Content styling
            content_style: `
                body {
                    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
                    font-size: 14px;
                }
            `,

            // Skin - auto-detect dark mode
            skin: document.documentElement.classList.contains('dark') ? 'oxide-dark' : 'oxide',
            content_css: document.documentElement.classList.contains('dark') ? 'dark' : 'default',

            // Preserve all HTML
            valid_elements: '*[*]',
            extended_valid_elements: '*[*]',
            valid_children: '+body[style],+body[script],+body[div]',
            verify_html: false,

            // Allow all attributes
            allow_unsafe_link_target: true,
            allow_script_urls: true,
            allow_html_in_named_anchor: true,

            // Image handling
            image_advtab: true,
            image_title: true,
            automatic_uploads: false,
            file_picker_types: 'image media',

            // Media embed
            media_live_embeds: true,

            // Code editor
            code_dialog_height: 450,
            code_dialog_width: 1000,

            // Quick toolbar
            quickbars_selection_toolbar: 'bold italic | quicklink h2 h3 blockquote',
            quickbars_insert_toolbar: false,

            // Context menu
            contextmenu: 'link image table',

            // Paste options
            paste_data_images: true,
            paste_as_text: false,

            // Browser spellcheck
            browser_spellcheck: true,

            // Status bar
            statusbar: editorType === 'full',
            elementpath: editorType === 'full',
            branding: false,
            promotion: false,

            // Disable premium features promotion
            toolbar_mode: 'sliding',
            toolbar_sticky: false,

            // Custom style formats (for full mode)
            style_formats: [
                { title: 'Headings', items: [
                    { title: 'Heading 1', format: 'h1' },
                    { title: 'Heading 2', format: 'h2' },
                    { title: 'Heading 3', format: 'h3' },
                    { title: 'Heading 4', format: 'h4' },
                    { title: 'Heading 5', format: 'h5' },
                    { title: 'Heading 6', format: 'h6' }
                ]},
                { title: 'Inline', items: [
                    { title: 'Bold', format: 'bold' },
                    { title: 'Italic', format: 'italic' },
                    { title: 'Underline', format: 'underline' },
                    { title: 'Strikethrough', format: 'strikethrough' },
                    { title: 'Code', format: 'code' }
                ]},
                { title: 'Blocks', items: [
                    { title: 'Paragraph', format: 'p' },
                    { title: 'Blockquote', format: 'blockquote' },
                    { title: 'Div', format: 'div' },
                    { title: 'Pre', format: 'pre' }
                ]},
                { title: 'Containers', items: [
                    { title: 'Card', block: 'div', classes: 'card', wrapper: true },
                    { title: 'Alert', block: 'div', classes: 'alert', wrapper: true },
                    { title: 'Button', inline: 'span', classes: 'button' }
                ]}
            ],

            // Setup callback
            setup: function(editor) {
                // Store editor reference
                window['tinymce-' + editorId] = editor;

                // Sync with textarea on change
                editor.on('change keyup paste', function() {
                    textareaElement.value = editor.getContent();

                    // Trigger form change detection
                    const event = new Event('input', { bubbles: true });
                    textareaElement.dispatchEvent(event);
                });

                // Add custom button for media modal
                editor.ui.registry.addButton('customMedia', {
                    icon: 'gallery',
                    tooltip: 'Media Library',
                    onAction: function() {
                        // Check if media modal function exists
                        if (typeof openMediaModal === 'function') {
                            const modalId = `tinyMediaModal_${editorId}`;
                            openMediaModal(modalId, false, 'all', `handleTinyMediaSelect_${editorId}`);
                        } else {
                            console.error('openMediaModal function not found');
                            alert('Media library is not available. Please ensure the media-modal component is loaded.');
                        }
                    }
                });

                // Create media selection handler
                window[`handleTinyMediaSelect_${editorId}`] = function(files) {
                    if (files.length > 0) {
                        const file = files[0];

                        // Insert based on file type
                        if (file.mime_type && file.mime_type.startsWith('image/')) {
                            // Insert image
                            editor.insertContent(`<img src="${file.url}" alt="${file.name || ''}" style="max-width: 100%;" />`);
                        } else if (file.mime_type && file.mime_type.startsWith('video/')) {
                            // Insert video
                            editor.insertContent(`
                                <video controls style="max-width: 100%;">
                                    <source src="${file.url}" type="${file.mime_type}">
                                    Your browser does not support the video tag.
                                </video>
                            `);
                        } else if (file.mime_type && file.mime_type.startsWith('audio/')) {
                            // Insert audio
                            editor.insertContent(`
                                <audio controls>
                                    <source src="${file.url}" type="${file.mime_type}">
                                    Your browser does not support the audio tag.
                                </audio>
                            `);
                        } else {
                            // Insert as link for other files
                            editor.insertContent(`<a href="${file.url}" target="_blank">${file.name || 'Download File'}</a>`);
                        }
                    }
                };
            },

            // Init callback
            init_instance_callback: function(editor) {
                // Set initial content if exists
                const initialContent = textareaElement.value;
                if (initialContent && initialContent.trim() !== '') {
                    try {
                        editor.setContent(initialContent);
                    } catch (error) {
                        console.error(`Error setting initial content for #${editorId}:`, error);
                    }
                } else {
                    console.log(`No initial content for #${editorId}`);
                }

                // Hide original textarea
                textareaElement.style.display = 'none';
            }
        });

        // Clean up on page unload
        window.addEventListener('beforeunload', function() {
            const editor = window['tinymce-' + editorId];
            if (editor) {
                tinymce.remove(editor);
            }
        });

        // Update textarea before form submission
        const form = textareaElement.closest('form');
        if (form) {
            form.addEventListener('submit', function() {
                const editor = window['tinymce-' + editorId];
                if (editor) {
                    textareaElement.value = editor.getContent();
                }
            });
        }
    });
</script>

<!-- Include the media modal component if it exists -->
@if(View::exists('components.media-modal'))
<x-media-modal
    :id="'tinyMediaModal_' . $editorId"
    title="Select Media for Editor"
    :multiple="false"
    allowedTypes="all"
    buttonText="Select Media"
    buttonClass="hidden"
/>
@endif
@endpush
