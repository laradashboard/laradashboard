@props([
    'placeholder' => __('Search...'),
    'model' => 'search',
    'class' => '',
])

<div
    class="relative flex items-center {{ $class }}"
    x-data="{ searchValue: '' }"
>
    <span class="pointer-events-none absolute left-3 flex">
        <iconify-icon icon="lucide:search" class="text-gray-400" width="18" height="18"></iconify-icon>
    </span>

    <input
        type="text"
        x-model="{{ $model }}"
        @input="searchValue = $event.target.value"
        placeholder="{{ $placeholder }}"
        {{ $attributes->merge(['class' => 'form-input pl-10 pr-10 py-2 text-sm w-full']) }}
        autocomplete="off"
    />

    {{-- Clear button --}}
    <button
        x-show="{{ $model }}.length > 0"
        x-cloak
        @click="{{ $model }} = ''; searchValue = ''"
        class="absolute right-2.5 top-1/2 inline-flex -translate-y-1/2 items-center justify-center rounded-full p-1 text-gray-400 transition-colors hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-700 dark:hover:text-gray-300"
        aria-label="{{ __('Clear search') }}"
        type="button"
    >
        <iconify-icon icon="lucide:x" width="16" height="16"></iconify-icon>
    </button>
</div>
