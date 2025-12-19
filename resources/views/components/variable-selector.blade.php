@props([
    'targetId' => '',
    'variables' => [],
    'label' => 'Add variable',
    'buttonId' => null,
    'dropdownContainerId' => null,
    'texteditor' => false,
])

@php
    $buttonId = $buttonId ?? 'var-btn-' . uniqid();
    $dropdownContainerId = $dropdownContainerId ?? 'dropdown-container-' . uniqid();
@endphp
<div class="w-full flex flex-row-reverse">
    <button type="button" id="{{ $buttonId }}" class="btn-default w-65">
        <span>{{ __($label) }}</span>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
    </button>

    <!-- Dropdown will be inserted here via JS -->
    <div id="{{ $dropdownContainerId }}" class="relative w-full"></div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const targetInput = document.getElementById('{{ $targetId }}');
    const dropdownBtn = document.getElementById('{{ $buttonId }}');
    const dropdownContainer = document.getElementById('{{ $dropdownContainerId }}');
    // Available variables
    const variables = @json($variables);

    // Whether this target is a rich text editor (TinyMCE) as passed from Blade.
    const isTextEditor = @json($texteditor);

    // Function to insert text at cursor position for normal inputs/textareas
    function insertAtCursor(text) {
        if (!targetInput) return;
        try {
            const startPos = (typeof targetInput.selectionStart === 'number') ? targetInput.selectionStart : (targetInput.value ? targetInput.value.length : 0);
            const endPos = (typeof targetInput.selectionEnd === 'number') ? targetInput.selectionEnd : startPos;
            const scrollTop = targetInput.scrollTop;

            targetInput.value = (targetInput.value || '').substring(0, startPos) +
                               text +
                               (targetInput.value || '').substring(endPos);

            targetInput.selectionStart = targetInput.selectionEnd = startPos + text.length;
            targetInput.scrollTop = scrollTop;
            targetInput.focus();
        } catch (err) {
            // Fallback: append text and focus
            targetInput.value = (targetInput.value || '') + text;
            targetInput.focus();
        }
    }

    // Create dropdown element.
    let dropdownElement = null;
    // Handler reference for outside-click removal so we can clean it up.
    let closeOnClickOutsideHandler = null;

    // Toggle dropdown function.
    function toggleDropdown() {
        // If dropdown exists, remove it and cleanup.
        if (dropdownElement) {
            if (dropdownElement.parentNode === dropdownContainer) {
                dropdownContainer.removeChild(dropdownElement);
            }
            dropdownElement = null;
            if (closeOnClickOutsideHandler) {
                document.removeEventListener('click', closeOnClickOutsideHandler);
                closeOnClickOutsideHandler = null;
            }
            return;
        }

        // Create dropdown.
        dropdownElement = document.createElement('div');
        dropdownElement.className =
            'absolute left-0 right-0 mt-1 bg-white dark:bg-gray-800 shadow-lg rounded-md border border-gray-200 dark:border-gray-700 z-[9999]';
        dropdownElement.style.maxHeight = '200px';
        dropdownElement.style.overflowY = 'auto';

        // Add header.
        const header = document.createElement('div');
        header.className =
            'p-2 text-sm font-medium border-b border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-700';
        header.textContent = '{{ __("Select a variable to insert") }}';
        dropdownElement.appendChild(header);

        // Add variables
        variables.forEach(variable => {
            const item = document.createElement('div');
            item.className =
                'px-4 py-1 cursor-pointer text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700';
            item.textContent = variable.label;
            item.dataset.value = variable.value;

            item.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const variableValue = this.dataset.value;

                if (isTextEditor) {
                    const tinyMceInstance = window['tinymce-{{ $targetId }}'];
                    if (tinyMceInstance) {
                        tinyMceInstance.focus();
                        tinyMceInstance.insertContent(`{${variableValue}}`);
                    } else {
                        // Fallback to inserting into the underlying textarea if TinyMCE instance not available yet
                        insertAtCursor(`{${variableValue}}`);
                    }
                } else {
                    insertAtCursor(`{${variableValue}}`);
                }

                // Close dropdown and cleanup
                if (dropdownElement && dropdownElement.parentNode === dropdownContainer) {
                    dropdownContainer.removeChild(dropdownElement);
                }
                dropdownElement = null;
                if (closeOnClickOutsideHandler) {
                    document.removeEventListener('click', closeOnClickOutsideHandler);
                    closeOnClickOutsideHandler = null;
                }
            });

            dropdownElement.appendChild(item);
        });

        // Add to container
        dropdownContainer.appendChild(dropdownElement);

        // Close dropdown when clicking outside
        closeOnClickOutsideHandler = function closeOnClickOutside(e) {
            if (dropdownElement && !dropdownElement.contains(e.target) && e.target !== dropdownBtn) {
                if (dropdownElement.parentNode === dropdownContainer) {
                    dropdownContainer.removeChild(dropdownElement);
                }
                dropdownElement = null;
                document.removeEventListener('click', closeOnClickOutsideHandler);
                closeOnClickOutsideHandler = null;
            }
        };
        document.addEventListener('click', closeOnClickOutsideHandler);
    }

    // Add click event to button
    dropdownBtn.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();

        if (isTextEditor) {
            const tinyMceInstance = window['tinymce-{{ $targetId }}'];
            if (tinyMceInstance) {
                tinyMceInstance.focus();
            } else if (targetInput) {
                targetInput.focus();
            }
        } else if (targetInput) {
            // Make sure textarea/input has focus before showing dropdown
            targetInput.focus();
        }

        toggleDropdown();
    });
});
</script>