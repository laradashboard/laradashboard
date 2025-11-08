@props([
    'targetId' => '',
    'variables' => [],
    'label' => 'Add variable',
    'buttonId' => null,
    'dropdownContainerId' => null
])

@php
    $buttonId = $buttonId ?? 'var-btn-' . uniqid();
    $dropdownContainerId = $dropdownContainerId ?? 'dropdown-container-' . uniqid();
@endphp

<button type="button" id="{{ $buttonId }}"
    class="ml-2 text-xs text-indigo-600 dark:text-indigo-400 border-none bg-transparent cursor-pointer focus:outline-none inline-flex items-center hover:text-indigo-700 dark:hover:text-indigo-300">
    <span>{{ __($label) }}</span>
    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 ml-1"
        fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M19 9l-7 7-7-7" />
    </svg>
</button>

<!-- Dropdown will be inserted here via JS -->
<div id="{{ $dropdownContainerId }}" class="relative w-full"></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const targetInput = document.getElementById('{{ $targetId }}');
    const dropdownBtn = document.getElementById('{{ $buttonId }}');
    const dropdownContainer = document.getElementById('{{ $dropdownContainerId }}');

    // Available variables
    const variables = @json($variables);

    // Function to insert text at cursor position
    function insertAtCursor(text) {
        // Get current cursor position
        const startPos = targetInput.selectionStart;
        const endPos = targetInput.selectionEnd;

        // Save scroll position
        const scrollTop = targetInput.scrollTop;

        // Insert text at cursor position
        targetInput.value = targetInput.value.substring(0, startPos) + 
                          text + 
                          targetInput.value.substring(endPos);

        // Restore cursor position after inserted text
        targetInput.selectionStart = targetInput.selectionEnd = startPos + text.length;

        // Restore scroll position
        targetInput.scrollTop = scrollTop;
    }

    // Create dropdown element
    let dropdownElement = null;

    // Toggle dropdown function
    function toggleDropdown() {
        // If dropdown exists, remove it
        if (dropdownElement) {
            dropdownContainer.removeChild(dropdownElement);
            dropdownElement = null;
            return;
        }

        // Create dropdown
        dropdownElement = document.createElement('div');
        dropdownElement.className =
            'absolute left-0 right-0 mt-1 bg-white dark:bg-gray-800 shadow-lg rounded-md border border-gray-200 dark:border-gray-700 z-[9999]';
        dropdownElement.style.maxHeight = '200px';
        dropdownElement.style.overflowY = 'auto';

        // Add header
        const header = document.createElement('div');
        header.className =
            'p-2 text-sm font-medium border-b border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-700';
        header.textContent = 'Select a variable to insert';
        dropdownElement.appendChild(header);

        // Add variables
        variables.forEach(variable => {
            const item = document.createElement('div');
            item.className =
                'px-4 py-1 cursor-pointer text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700';
            item.textContent = variable.label;
            item.dataset.value = variable.value;

            item.addEventListener('click', function() {
                const variableValue = this.dataset.value;
                
                // Make sure textarea has focus first
                targetInput.focus();
                
                // Check if this is a Quill editor
                const quillInstance = window['quill-{{ $targetId }}'];
                if (quillInstance) {
                    // Insert into Quill editor
                    const range = quillInstance.getSelection();
                    const index = range ? range.index : quillInstance.getLength();
                    quillInstance.insertText(index, variableValue);
                    quillInstance.setSelection(index + variableValue.length);
                } else {
                    // Insert at current cursor position and maintain focus
                    insertAtCursor(variableValue);
                    
                    // Trigger change event
                    const event = new Event('change');
                    targetInput.dispatchEvent(event);
                }

                // Remove dropdown
                dropdownContainer.removeChild(dropdownElement);
                dropdownElement = null;
            });

            dropdownElement.appendChild(item);
        });

        // Add to container
        dropdownContainer.appendChild(dropdownElement);

        // Close dropdown when clicking outside
        document.addEventListener('click', function closeOnClickOutside(e) {
            if (dropdownElement && !dropdownElement.contains(e.target) && e.target !== dropdownBtn) {
                dropdownContainer.removeChild(dropdownElement);
                dropdownElement = null;
                document.removeEventListener('click', closeOnClickOutside);
            }
        });
    }

    // Add click event to button
    dropdownBtn.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        // Make sure textarea has focus before showing dropdown
        targetInput.focus();
        
        toggleDropdown();
    });
});
</script>