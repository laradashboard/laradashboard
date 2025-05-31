@csrf

<div x-data="slugGenerator('{{ old('name', $term ? $term->name : '') }}', '{{ old('slug', $term ? $term->slug : '') }}')">
    <!-- Name -->
    <div>
        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Name') }}</label>
        <input type="text" name="name" id="name" required x-model="title"
            class="form-control">
    </div>

    <!-- Slug -->
    <div class="mt-2">
        <label for="slug" class="block text-sm font-medium text-gray-700 dark:text-gray-400">
            {{ __('Slug') }}
            <button type="button" @click="toggleSlugEdit" class="ml-2 text-xs text-gray-500 dark:text-gray-400 hover:text-brand-500 dark:hover:text-brand-400">
                <span x-show="!showSlugEdit">{{ __('Edit') }}</span>
                <span x-show="showSlugEdit">{{ __('Hide') }}</span>
            </button>
        </label>
        <div class="relative">
            <input type="text" name="slug" id="slug" x-model="slug" x-bind:readonly="!showSlugEdit"
                class="form-control"
                placeholder="{{ __('Leave empty to auto-generate') }}"
                x-bind:class="{'bg-gray-50 dark:bg-gray-800': !showSlugEdit}">
            <button type="button" @click="generateSlug" x-show="showSlugEdit"
                class="absolute right-2 top-1/2 -translate-y-1/2 rounded-md bg-gray-100 px-2 py-1 text-xs text-gray-600 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                {{ __('Generate') }}
            </button>
        </div>
    </div>

    <!-- Description -->
    <div class="mt-2">
        <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Description') }}</label>
        <textarea name="description" id="description" rows="3" 
            class="form-control !h-30">{{ old('description', $term ? $term->description : '') }}</textarea>
    </div>

    @if($taxonomyModel->show_featured_image)
    <!-- Featured Image -->
    <div class="mt-4">
        <label for="featured_image" class="block text-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Featured Image') }}</label>
        @if($term && $term->featured_image)
            <div class="mb-4">
                <img src="{{ Storage::url($term->featured_image) }}" alt="{{ $term->name }}" class="max-h-48 rounded-lg border dark:border-gray-700">
                <div class="mt-2">
                    <label class="flex items-center">
                        <input type="checkbox" name="remove_featured_image" id="remove_featured_image" value="1" class="mr-2">
                        <span class="text-sm text-gray-700 dark:text-gray-400">{{ __('Remove featured image') }}</span>
                    </label>
                </div>
            </div>
        @endif
        <input type="file" name="featured_image" id="featured_image" accept="image/*"
            class="focus:border-ring-brand-300 cursor-pointer focus:file:ring-brand-300 w-full overflow-hidden rounded-lg border border-gray-300 bg-transparent text-sm text-gray-500 transition-colors file:mr-5 file:border-collapse file:cursor-pointer file:rounded-l-lg file:border-0 file:border-r file:border-solid file:border-gray-200 file:bg-gray-50 file:py-3 file:px-4 file:text-sm file:text-gray-700 placeholder:text-gray-400 hover:file:bg-gray-100 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400 dark:file:border-gray-800 dark:file:bg-white/[0.03] dark:file:text-gray-400 px-4">
    </div>
    @endif

    @if($taxonomyModel->hierarchical)
    <!-- Parent -->
    <div class="mt-2">
        @php
            function buildHierarchicalOptions($terms, $parentId = null, $depth = 0, $currentTermId = null) {
                $options = [];
                foreach ($terms as $term) {
                    if ($term->parent_id == $parentId && (!$currentTermId || $term->id !== $currentTermId)) {
                        $indent = str_repeat('— ', $depth);
                        $options[] = [
                            'value' => $term->id,
                            'label' => $indent . $term->name
                        ];
                        $childOptions = buildHierarchicalOptions($terms, $term->id, $depth + 1, $currentTermId);
                        $options = array_merge($options, $childOptions);
                    }
                }
                return $options;
            }
            
            $parentOptions = [];
            $hierarchicalOptions = buildHierarchicalOptions($parentTerms, null, 0, $term ? $term->id : null);
            $parentOptions = array_merge($parentOptions, $hierarchicalOptions);
        @endphp
        
        <x-inputs.combobox 
            name="parent_id"
            :label="__('Parent') . ' ' . $taxonomyModel->label_singular"
            :placeholder="__('Select Parent')"
            :options="$parentOptions"
            :selected="old('parent_id', $term ? $term->parent_id : null)"
            :searchable="false"
        />
    </div>
    @endif

    <!-- Submit Button -->
    <div class="flex gap-4 mt-4">
        <button type="submit" class="btn-primary">
            {{ $term ? __('Update') : __('Add New') }} {{ $taxonomyModel->label_singular }}
        </button>
        @if($term)
            <a href="{{ route('admin.terms.index', $taxonomy) }}" class="btn-default">
                {{ __('Cancel') }}
            </a>
        @endif
    </div>
</div>
