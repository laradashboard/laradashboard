@props([
    'name' => 'tags',
    'label' => 'Tags',
    'value' => '',
    'placeholder' => 'Add and press Enter',
    'hint' => 'Press Enter to add'
])

@php
    // Handle different formats of tags (array, JSON string, comma-separated string)
    $processedValue = '';
    
    if ($value) {
        if (is_array($value)) {
            // If already an array, implode to comma-separated
            $processedValue = implode(', ', $value);
        } elseif (is_string($value) && substr($value, 0, 1) === '[' && substr($value, -1) === ']') {
            // If it's a JSON string, decode and implode
            try {
                $decodedValue = json_decode($value, true);
                if (is_array($decodedValue)) {
                    $processedValue = implode(', ', $decodedValue);
                } else {
                    $processedValue = $value; // Keep original if not valid JSON array
                }
            } catch (\Exception $e) {
                $processedValue = $value; // Keep original if JSON decode fails
            }
        } else {
            // Already a string format
            $processedValue = $value;
        }
    }
@endphp

<div {{ $attributes->merge(['class' => 'rounded-lg border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800 shadow-sm']) }}>
    <div class="p-4">
        <!-- Hidden input to store tags -->
        <input type="hidden" id="{{ $name }}" name="{{ $name }}" value="{{ $processedValue }}">
        
        <!-- Visual tags input -->
        <div class="mb-2">
            <div class="flex flex-wrap items-center gap-2 mb-2" id="{{ $name }}-container">
                <!-- Tags will be rendered here by JavaScript -->
            </div>
            
            <div class="relative">
                <input type="text" id="{{ $name }}-input" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary dark:bg-gray-700 dark:border-gray-600 dark:text-white" 
                    placeholder="{{ __($placeholder) }}">
            </div>
        </div>
        <p class="flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400">
            <iconify-icon icon="heroicons:information-circle" class="h-4 w-4 mt-2"></iconify-icon>
            {{ __($hint) }}
        </p>
    </div>
</div>

@once
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                initializeTagInputs();
            });

            function initializeTagInputs() {
                // Find all tag inputs on the page
                document.querySelectorAll('[id$="-input"][id^="{{ $name }}"]').forEach(tagInput => {
                    const inputName = tagInput.id.replace('-input', '');
                    const tagsInput = document.getElementById(inputName);
                    const tagsContainer = document.getElementById(inputName + '-container');
                    
                    if (!tagsInput || !tagsContainer) return;
                    
                    // Initialize existing tags
                    if (tagsInput.value) {
                        let existingTags = [];
                        
                        // Try to parse as JSON first
                        try {
                            if (tagsInput.value.trim().startsWith('[') && tagsInput.value.trim().endsWith(']')) {
                                existingTags = JSON.parse(tagsInput.value);
                            } else {
                                // Fall back to comma-separated format
                                existingTags = tagsInput.value.split(',').map(tag => tag.trim()).filter(Boolean);
                            }
                        } catch (e) {
                            // If JSON parsing fails, use comma-separated format
                            existingTags = tagsInput.value.split(',').map(tag => tag.trim()).filter(Boolean);
                        }
                        
                        existingTags.forEach(tag => {
                            createTagBadge(tag, tagsInput, tagsContainer);
                        });
                    }
                    
                    // Handle Enter key in tag input
                    tagInput.addEventListener('keydown', function(e) {
                        if (e.key === 'Enter') {
                            e.preventDefault();
                            const tag = tagInput.value.trim();
                            if (tag) {
                                addTag(tag, tagsInput, tagsContainer);
                                tagInput.value = '';
                            }
                        }
                    });
                });
            }
            
            // Function to add a tag
            function addTag(tag, tagsInput, tagsContainer) {
                let tagsArray = [];
                const currentTags = tagsInput.value.trim();
                
                // Try to parse as JSON first
                try {
                    if (currentTags.startsWith('[') && currentTags.endsWith(']')) {
                        tagsArray = JSON.parse(currentTags);
                    } else {
                        // Fall back to comma-separated format
                        tagsArray = currentTags ? currentTags.split(',').map(t => t.trim()) : [];
                    }
                } catch (e) {
                    // If JSON parsing fails, use comma-separated format
                    tagsArray = currentTags ? currentTags.split(',').map(t => t.trim()) : [];
                }
                
                // Check if tag already exists
                if (!tagsArray.includes(tag)) {
                    // Add to array
                    tagsArray.push(tag);
                    
                    // Update hidden input with comma-separated value (for form submission)
                    tagsInput.value = tagsArray.join(', ');
                    
                    // Create visual badge
                    createTagBadge(tag, tagsInput, tagsContainer);
                }
            }
            
            // Function to create a tag badge
            function createTagBadge(tag, tagsInput, tagsContainer) {
                const badge = document.createElement('span');
                badge.className = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-100 text-primary-800 dark:bg-primary-900 dark:text-primary-200';
                badge.innerHTML = `${tag} <button type="button" class="ml-1 inline-flex items-center justify-center h-4 w-4 rounded-full hover:bg-primary-200 dark:hover:bg-primary-800 focus:outline-none">
                    <iconify-icon icon="heroicons:x-mark" class="h-3 w-3"></iconify-icon>
                    <span class="sr-only">Remove tag</span>
                </button>`;
                
                // Add click handler to remove tag
                badge.addEventListener('click', function() {
                    removeTag(tag, tagsInput);
                    badge.remove();
                });
                
                tagsContainer.appendChild(badge);
            }
            
            // Function to remove a tag
            function removeTag(tagToRemove, tagsInput) {
                const currentTags = tagsInput.value.trim();
                if (currentTags) {
                    let tagsArray = [];
                    
                    // Try to parse as JSON first
                    try {
                        if (currentTags.startsWith('[') && currentTags.endsWith(']')) {
                            tagsArray = JSON.parse(currentTags);
                        } else {
                            // Fall back to comma-separated format
                            tagsArray = currentTags.split(',').map(t => t.trim());
                        }
                    } catch (e) {
                        // Fall back to comma-separated format
                        tagsArray = currentTags.split(',').map(t => t.trim());
                    }
                    
                    tagsArray = tagsArray.filter(tag => tag !== tagToRemove);
                    tagsInput.value = tagsArray.join(', ');
                }
            }
        </script>
    @endpush
@endonce
