<form
    action="{{ $term ? route('admin.terms.update', [$taxonomy, $term->id]) : route('admin.terms.store', $taxonomy) }}"
    method="POST"
    enctype="multipart/form-data"
    data-prevent-unsaved-changes
>
    @method($term ? 'PUT' : 'POST')
    @csrf

    {!! Hook::applyFilters(TermFilterHook::TERM_FORM_START, '', $term ?? null, $taxonomy) !!}

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
        <x-card class="{{ $term ? 'md:col-span-2' : 'md:col-span-3' }}">
            <x-slot name="header">
                {{ __('Details') }}
            </x-slot>
            <div x-data="slugGenerator('{{ old('name', $term ? $term->name : '') }}', '{{ old('slug', $term ? $term->slug : '') }}')">
                <div class="space-y-1">
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ __('Name') }}
                        <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" id="name" required x-model="title" class="form-control">
                </div>
                {!! Hook::applyFilters(TermFilterHook::TERM_FORM_AFTER_NAME, '', $term ?? null, $taxonomy) !!}

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
                {!! Hook::applyFilters(TermFilterHook::TERM_FORM_AFTER_SLUG, '', $term ?? null, $taxonomy) !!}

                <!-- Description -->
                <div class="mt-2 space-y-1">
                    <label for="description"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Description') }}</label>
                    <textarea name="description" id="description" rows="3" class="form-control !h-16">{{ old('description', $term ? $term->description : '') }}</textarea>
                </div>
                {!! Hook::applyFilters(TermFilterHook::TERM_FORM_AFTER_DESCRIPTION, '', $term ?? null, $taxonomy) !!}

                @if(empty($term))
                @include('backend.pages.terms.partials.term-additional-settings')
                @endif
            </div>
        </x-card>

        @if($term)
        <x-card>
            <x-slot name="header">
                {{ __('Additional Settings') }}
            </x-slot>
            @include('backend.pages.terms.partials.term-additional-settings')
        </x-card>
        @endif

        {!! Hook::applyFilters(TermFilterHook::TERM_FORM_AFTER_ADDITIONAL_SETTINGS, '', $term ?? null, $taxonomy) !!}

        {!! Hook::applyFilters(TermFilterHook::TERM_FORM_END, '', $term ?? null, $taxonomy) !!}

        <x-buttons.submit-buttons cancelUrl="{{ $term ? route('admin.terms.index', $taxonomy) : '' }}" />
    </div>
</form>