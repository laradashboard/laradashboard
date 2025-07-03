@props([
    'label' => 'File',
    'name' => 'file',
    'id' => null,
    'multiple' => false,
    'existingAttachment' => null,
    'existingAltText' => '',
    'removeCheckboxName' => 'remove_featured_image',
    'removeCheckboxLabel' => 'Remove featured image',
])

@php
    $id = $id ?? $name;
@endphp

<div {{ $attributes->merge(['class' => 'mb-4']) }}>
    <label for="{{ $id }}"
        class="block text-sm font-medium text-gray-700 dark:text-gray-400">{{ $label }}</label>
    @if ($existingAttachment)
        <div class="mb-4">
            <img src="{{ $existingAttachment }}" alt="{{ $existingAltText }}" class="max-h-48 rounded-lg">
            <div class="mt-2">
                <label class="flex items-center">
                    <input type="checkbox" name="{{ $removeCheckboxName }}" id="{{ $removeCheckboxName }}"
                        class="mr-2">
                    <span
                        class="text-sm text-gray-700 dark:text-gray-400">{{ $removeCheckboxLabel }}</span>
                </label>
            </div>
        </div>
    @endif
    <input type="file" name="{{ $name }}" id="{{ $id }}" {{ $multiple ? 'multiple' : '' }}
        class="focus:border-ring-brand-300 cursor-pointer focus:file:ring-brand-300 w-full overflow-hidden rounded-lg border border-gray-300 bg-transparent text-sm text-gray-500 transition-colors file:mr-5 file:border-collapse file:cursor-pointer file:rounded-l-lg file:border-0 file:border-r file:border-solid file:border-gray-200 file:bg-gray-50 file:py-3 file:px-4 file:text-sm file:text-gray-700 placeholder:text-gray-400 hover:file:bg-gray-100 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400 dark:file:border-gray-800 dark:file:bg-white/[0.03] dark:file:text-gray-400 px-4">
    {{ $slot }}
</div>
