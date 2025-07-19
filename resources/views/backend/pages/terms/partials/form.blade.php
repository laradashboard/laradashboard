@csrf

<div x-data="slugGenerator('{{ old('name', $term ? $term->name : '') }}', '{{ old('slug', $term ? $term->slug : '') }}')">
    <div class="space-y-1">
        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
            {{ __('Name') }}
            <span class="text-red-500">*</span>
        </label>
        <input type="text" name="name" id="name" required x-model="title" class="form-control">
    </div>

    <div class="mt-2 space-y-1">
        <label for="slug" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
            {{ __('Slug') }}
            <button type="button" @click="toggleSlugEdit"
                class="ml-2 text-xs text-gray-500 dark:text-gray-300 hover:text-brand-500 dark:hover:text-brand-400">
                <span x-show="!showSlugEdit">{{ __('Edit') }}</span>
                <span x-show="showSlugEdit">{{ __('Hide') }}</span>
            </button>
        </label>
        <div class="relative">
            <input type="text" name="slug" id="slug" x-model="slug" x-bind:readonly="!showSlugEdit"
                class="form-control" placeholder="{{ __('Leave empty to auto-generate') }}"
                x-bind:class="{ 'bg-gray-50 dark:bg-gray-800': !showSlugEdit }">
            <button type="button" @click="generateSlug" x-show="showSlugEdit"
                class="absolute right-2 top-1/2 -translate-y-1/2 rounded-md bg-gray-100 px-2 py-1 text-xs text-gray-600 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                {{ __('Generate') }}
            </button>
        </div>
    </div>

    <!-- Description -->
    <div class="mt-2 space-y-1">
        <label for="description"
            class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Description') }}</label>
        <textarea name="description" id="description" rows="3" class="form-control !h-30">{{ old('description', $term ? $term->description : '') }}</textarea>
    </div>
    @if ($taxonomyModel->show_featured_image)
        <div class="mt-2">
            <x-inputs.file-input name="featured_image" :existingAttachment="$term ? $term->featured_image : null"
                :existingAltText="$term ? $term->featured_image_alt_text : ''" removeCheckboxName="remove_featured_image"
                removeCheckboxLabel="{{ __('Remove featured image') }}" />
        </div>
    @endif

    @if ($taxonomyModel->hierarchical)
        <div class="mt-2">
            <x-posts.term-selector name="parent_id" :taxonomyModel="$taxonomyModel" :term="$term" :parentTerms="$parentTerms"
                :placeholder="__('Select Parent ' . $taxonomyModel->label_singular)" :label='__("Parent {$taxonomyModel->label_singular}")' searchable="false" />
        </div>
    @endif
    <div class="mt-4">
        <x-buttons.submit-buttons cancelUrl="{{ $term ? route('admin.terms.index', $taxonomy) : '' }}" />
    </div>
</div>
