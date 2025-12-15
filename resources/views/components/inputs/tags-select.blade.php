@props([
    'name' => 'tags',
    'label' => '',
    'placeholder' => 'Search or add tag...',
    'availableTags' => [],
    'selectedTags' => [],
    'allowCreate' => true,
    'saveUrl' => null,
    'fieldType' => 'tags',
    'class' => '',
])

@php
    $componentId = 'tags-select-' . uniqid();
    $alpineId = str_replace('-', '_', $componentId);

    // Normalize tags to array format
    if ($availableTags instanceof \Illuminate\Support\Collection) {
        $availableTags = $availableTags->toArray();
    }
    if ($selectedTags instanceof \Illuminate\Support\Collection) {
        $selectedTags = $selectedTags->toArray();
    }

    // Ensure tags have proper structure
    $normalizedAvailable = collect($availableTags)->map(function ($tag) {
        if (is_array($tag)) {
            return [
                'id' => $tag['id'] ?? $tag['value'] ?? null,
                'name' => $tag['name'] ?? $tag['label'] ?? '',
                'color' => $tag['color'] ?? '#3b82f6',
            ];
        }
        return ['id' => $tag, 'name' => (string)$tag, 'color' => '#3b82f6'];
    })->toArray();

    $normalizedSelected = collect($selectedTags)->map(function ($tag) {
        if (is_array($tag)) {
            return [
                'id' => $tag['id'] ?? $tag['value'] ?? null,
                'name' => $tag['name'] ?? $tag['label'] ?? '',
                'color' => $tag['color'] ?? '#3b82f6',
            ];
        }
        return ['id' => $tag, 'name' => (string)$tag, 'color' => '#3b82f6'];
    })->toArray();
@endphp

<div
    x-data="tagsSelect_{{ $alpineId }}()"
    x-init="init()"
    class="w-full {{ $class }}"
    {{ $attributes }}
>
    @if($label)
        <label class="form-label mb-2">{{ __($label) }}</label>
    @endif

    <div class="flex flex-wrap gap-2 mb-3">
        <template x-for="tag in selectedTags" :key="tag.id">
            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium text-white"
                :style="{ backgroundColor: tag.color || '#3b82f6' }">
                <span x-text="tag.name"></span>
                <button @click.prevent.stop="removeTag(tag.id)" type="button" class="hover:opacity-75 focus:outline-none">
                    <iconify-icon icon="lucide:x" class="text-xs"></iconify-icon>
                </button>
            </span>
        </template>
        <span x-show="selectedTags.length === 0" class="text-sm text-gray-500 dark:text-gray-400">
            {{ __('No tags') }}
        </span>
    </div>

    <div class="relative" x-ref="container">
        <input
            type="text"
            x-model="searchQuery"
            x-ref="searchInput"
            @mousedown.stop="toggleDropdown()"
            @keydown.enter.prevent="handleEnter()"
            @keydown.escape.prevent="closeDropdown()"
            @keydown.arrow-down.prevent="focusNextOption()"
            @keydown.arrow-up.prevent="focusPrevOption()"
            class="form-control form-control-sm w-full"
            placeholder="{{ __($placeholder) }}"
            autocomplete="off"
        >

        <div
            x-show="isOpen"
            x-cloak
            x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            @mousedown.outside="closeDropdown()"
            class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg max-h-48 overflow-y-auto"
        >
            <template x-for="(tag, index) in filteredTags" :key="tag.id">
                <button
                    type="button"
                    @mousedown.prevent.stop="toggleTag(tag)"
                    :class="{ 'bg-gray-100 dark:bg-gray-700': focusedIndex === index }"
                    class="w-full px-3 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center justify-between focus:outline-none"
                >
                    <span class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full shrink-0" :style="{ backgroundColor: tag.color || '#3b82f6' }"></span>
                        <span x-text="tag.name"></span>
                    </span>
                    <iconify-icon x-show="isSelected(tag.id)" icon="lucide:check" class="text-green-500"></iconify-icon>
                </button>
            </template>

            @if($allowCreate)
            <button
                type="button"
                x-show="searchQuery.trim() && !tagExists(searchQuery)"
                @mousedown.prevent.stop="createTag()"
                class="w-full px-3 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-2 border-t border-gray-200 dark:border-gray-700 focus:outline-none"
            >
                <iconify-icon icon="lucide:plus" class="text-primary"></iconify-icon>
                <span>{{ __('Create') }} "<span x-text="searchQuery"></span>"</span>
            </button>
            @endif

            <div x-show="filteredTags.length === 0 && !searchQuery.trim()" class="px-3 py-2 text-sm text-gray-500 dark:text-gray-400">
                {{ __('No tags available') }}
            </div>
        </div>
    </div>

    <template x-for="(tag, index) in selectedTags" :key="'hidden-' + tag.id">
        <input type="hidden" :name="'{{ $name }}[' + index + ']'" :value="tag.id">
    </template>
</div>

@once
@push('scripts')
<script>
function tagsSelect_{{ $alpineId }}() {
    return {
        availableTags: @json($normalizedAvailable),
        selectedTags: @json($normalizedSelected),
        searchQuery: '',
        isOpen: false,
        focusedIndex: -1,
        saveUrl: @json($saveUrl),
        fieldType: @json($fieldType),

        init() {
            // Close dropdown when clicking outside
            this.$watch('isOpen', (value) => {
                if (value) {
                    this.focusedIndex = -1;
                }
            });
        },

        get filteredTags() {
            if (!this.searchQuery.trim()) {
                return this.availableTags;
            }
            const query = this.searchQuery.toLowerCase();
            return this.availableTags.filter(tag =>
                tag.name.toLowerCase().includes(query)
            );
        },

        toggleDropdown() {
            this.isOpen = !this.isOpen;
        },

        openDropdown() {
            this.isOpen = true;
        },

        closeDropdown() {
            this.isOpen = false;
            this.focusedIndex = -1;
        },

        isSelected(tagId) {
            return this.selectedTags.some(t => t.id === tagId);
        },

        tagExists(name) {
            const query = name.toLowerCase().trim();
            return this.availableTags.some(t => t.name.toLowerCase() === query);
        },

        toggleTag(tag) {
            if (this.isSelected(tag.id)) {
                this.removeTag(tag.id);
            } else {
                this.selectedTags.push({...tag});
                this.saveTags();
            }
            this.searchQuery = '';
        },

        removeTag(tagId) {
            this.selectedTags = this.selectedTags.filter(t => t.id !== tagId);
            this.saveTags();
        },

        createTag() {
            const tagName = this.searchQuery.trim();
            if (!tagName || this.tagExists(tagName)) return;

            // Send the new tag name, backend will create it
            const tagIds = this.selectedTags.map(t => t.id);
            tagIds.push(tagName); // Push the name for new tag creation

            this.searchQuery = '';
            this.saveTagsWithData(tagIds);
        },

        handleEnter() {
            if (this.focusedIndex >= 0 && this.focusedIndex < this.filteredTags.length) {
                this.toggleTag(this.filteredTags[this.focusedIndex]);
            } else if (this.searchQuery.trim() && !this.tagExists(this.searchQuery)) {
                this.createTag();
            }
        },

        focusNextOption() {
            if (!this.isOpen) {
                this.openDropdown();
                return;
            }
            if (this.focusedIndex < this.filteredTags.length - 1) {
                this.focusedIndex++;
            }
        },

        focusPrevOption() {
            if (this.focusedIndex > 0) {
                this.focusedIndex--;
            }
        },

        saveTags() {
            const tagIds = this.selectedTags.map(t => t.id);
            this.saveTagsWithData(tagIds);
        },

        saveTagsWithData(data) {
            if (!this.saveUrl) {
                // Dispatch event for parent to handle
                this.$dispatch('tags-updated', { tags: this.selectedTags, ids: data });
                return;
            }

            fetch(this.saveUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    field_type: this.fieldType,
                    content: data
                })
            })
            .then(response => response.json())
            .then(result => {
                if (result.success && result.tags) {
                    this.selectedTags = result.tags;
                    // Add any new tags to available list
                    result.tags.forEach(tag => {
                        if (!this.availableTags.some(t => t.id === tag.id)) {
                            this.availableTags.push(tag);
                        }
                    });
                }
                this.$dispatch('tags-saved', { success: result.success, tags: this.selectedTags });
            })
            .catch(error => {
                console.error('Error saving tags:', error);
                this.$dispatch('tags-error', { error: error });
            });
        }
    };
}
</script>
@endpush
@endonce
