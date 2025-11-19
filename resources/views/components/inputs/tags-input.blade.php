@props([
    'name' => 'tags',
    'label' => __('Tags'),
    'value' => '',
    'placeholder' => __('Add and press Enter'),
    'hint' => '',
])

@php
    $processedValue = '';

    if ($value) {
        if (is_array($value)) {
            // If already an array, implode to comma-separated
            $processedValue = implode(', ', $value);
        } elseif (is_string($value) && str_starts_with($value, '[') && str_ends_with($value, ']')) {
            try {
                $decodedValue = json_decode($value, true, 512, JSON_THROW_ON_ERROR);
                if (is_array($decodedValue)) {
                    $processedValue = implode(', ', $decodedValue);
                } else {
                    $processedValue = $value; // Keep original if not valid JSON array
                }
            } catch (\JsonException $e) {
                $processedValue = $value;
            }
        } else {
            // Already a string format
            $processedValue = $value;
        }
    }
@endphp

<div class="mb-4">
    @if($label)
        <label class="form-label" for="{{ $name }}">{{ __($label) }}</label>
    @endif
    
    <div
        {{ $attributes->merge(['class' => 'rounded-md bg-white dark:bg-white/[0.03]']) }}>
        <div>
            <input type="hidden" id="{{ $name }}" name="{{ $name }}" value="{{ $processedValue }}">
            <div class="mb-2">
                <div class="flex flex-wrap items-center gap-2 mb-2" id="{{ $name }}-container">
                </div>

                <div class="relative">
                    <input type="text" id="{{ $name }}-input" class="form-control" placeholder="{{ __($placeholder) }}">
                </div>
            </div>
        </div>
    </div>
    
    @if($hint)
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __($hint) }}</p>
    @endif
</div>

@once
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                initializeTagInputs();
            });

            function initializeTagInputs() {
                document.querySelectorAll('[id$="-input"]').forEach(tagInput => {
                    const inputName = tagInput.id.replace('-input', '');
                    const tagsInput = document.getElementById(inputName);
                    const tagsContainer = document.getElementById(inputName + '-container');

                    if (!tagsInput || !tagsContainer) return;

                    if (tagsInput.value) {
                        let existingTags = [];
                        try {
                            if (tagsInput.value.trim().startsWith('[') && tagsInput.value.trim().endsWith(']')) {
                                existingTags = JSON.parse(tagsInput.value);
                            } else {
                                existingTags = tagsInput.value.split(',').map(tag => tag.trim()).filter(Boolean);
                            }
                        } catch (e) {
                            existingTags = tagsInput.value.split(',').map(tag => tag.trim()).filter(Boolean);
                        }

                        existingTags.forEach(tag => {
                            createTagBadge(tag, tagsInput, tagsContainer);
                        });
                    }

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

            function addTag(tag, tagsInput, tagsContainer) {
                let tagsArray = [];
                const currentTags = tagsInput.value.trim();
                try {
                    if (currentTags.startsWith('[') && currentTags.endsWith(']')) {
                        tagsArray = JSON.parse(currentTags);
                    } else {
                        tagsArray = currentTags ? currentTags.split(',').map(t => t.trim()) : [];
                    }
                } catch (e) {
                    tagsArray = currentTags ? currentTags.split(',').map(t => t.trim()) : [];
                }

                if (!tagsArray.includes(tag)) {
                    tagsArray.push(tag);
                    tagsInput.value = tagsArray.join(', ');
                    createTagBadge(tag, tagsInput, tagsContainer);
                }
            }

            function createTagBadge(tag, tagsInput, tagsContainer) {
                const badge = document.createElement('span');
                badge.className = 'badge';
                
                // Create tag text span to safely add content
                const tagText = document.createElement('span');
                tagText.textContent = tag;
                badge.appendChild(tagText);
                
                // Create remove button separately
                const removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.className = 'ml-1 inline-flex items-center justify-center h-4 w-4 rounded-full hover:bg-primary-200 dark:hover:bg-primary-800 focus:outline-none';
                removeBtn.innerHTML = '<iconify-icon icon="heroicons:x-mark" class="h-3 w-3"></iconify-icon><span class="sr-only">Remove tag</span>';
                
                // Add event listener to the button only
                removeBtn.addEventListener('click', function() {
                    removeTag(tag, tagsInput);
                    badge.remove();
                });
                
                badge.appendChild(removeBtn);
                tagsContainer.appendChild(badge);
            }

            function removeTag(tagToRemove, tagsInput) {
                const currentTags = tagsInput.value.trim();
                if (currentTags) {
                    let tagsArray = [];
                    try {
                        if (currentTags.startsWith('[') && currentTags.endsWith(']')) {
                            tagsArray = JSON.parse(currentTags);
                        } else {
                            tagsArray = currentTags.split(',').map(t => t.trim());
                        }
                    } catch (e) {
                        tagsArray = currentTags.split(',').map(t => t.trim());
                    }

                    tagsArray = tagsArray.filter(tag => tag !== tagToRemove);
                    tagsInput.value = tagsArray.join(', ');
                }
            }
        </script>
    @endpush
@endonce
