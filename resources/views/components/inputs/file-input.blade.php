@props([
    'label' => __('File'),
    'name' => 'file',
    'id' => null,
    'multiple' => false,
    'existingAttachment' => null,
    'existingAltText' => '',
    'removeCheckboxName' => 'remove_featured_image',
    'removeCheckboxLabel' => null,
    'selectedImageClass' => null,
    'accept' => null,
    'hint' => null,
])

@php
    $id = $id ?? $name;
@endphp

<div class="space-y-1">
    <label for="{{ $id }}" class="form-label">{{ $label }}</label>
    @if ($existingAttachment)
        <div class="mb-4 {{ $selectedImageClass ?? '' }}">
            <img src="{{ $existingAttachment }}" alt="{{ $existingAltText }}" class="max-h-48 rounded-md">

            @if($removeCheckboxLabel)
                <div class="mt-2">
                    <label class="flex items-center">
                        <input type="checkbox" name="{{ $removeCheckboxName }}" id="{{ $removeCheckboxName }}"
                            class="form-checkbox mr-2">
                        <span
                            class="text-sm text-gray-700 dark:text-gray-300">{{ $removeCheckboxLabel }}</span>
                    </label>
                </div>
            @endif
        </div>
    @endif
    <input
        type="file"
        name="{{ $name }}"
        id="{{ $id }}"
        {{ $multiple ? 'multiple' : '' }}
        @if($accept) accept="{{ $accept }}" @endif
        {{ $attributes->whereStartsWith('wire:') }}
        class="form-control-file"
    >
    @if($hint)
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $hint }}</p>
    @endif
    @error($name)
        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror
    {{ $slot }}
</div>
