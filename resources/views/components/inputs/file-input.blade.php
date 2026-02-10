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
    'avatarStyle' => false,
    'avatarSize' => 'lg',
])

@php
    $id = $id ?? $name;
    $avatarSizeClasses = match($avatarSize) {
        'sm' => 'size-16',
        'md' => 'size-24',
        'lg' => 'size-32',
        'xl' => 'size-40',
        default => 'size-32',
    };
@endphp

<div class="space-y-1">
    @if($label)
        <label for="{{ $id }}" class="form-label">{{ $label }}</label>
    @endif
    @if($avatarStyle)
        <div
            x-data="{
                preview: '{{ $existingAttachment ?? '' }}',
                updatePreview(event) {
                    const file = event.target.files[0];
                    if (file) {
                        this.preview = URL.createObjectURL(file);
                    }
                }
            }"
            class="flex flex-col items-center gap-3 {{ $selectedImageClass ?? '' }}"
        >
            <label for="{{ $id }}" class="cursor-pointer inline-block">
                <div class="relative group">
                    <template x-if="preview">
                        <img
                            :src="preview"
                            alt="{{ $existingAltText }}"
                            class="{{ $avatarSizeClasses }} rounded-full object-cover ring-4 ring-gray-100 dark:ring-gray-700 shadow-lg"
                        >
                    </template>
                    <template x-if="!preview">
                        <div class="{{ $avatarSizeClasses }} rounded-full bg-gray-100 dark:bg-gray-700 ring-4 ring-gray-100 dark:ring-gray-700 shadow-lg flex items-center justify-center">
                            <iconify-icon icon="lucide:user" class="text-gray-400 dark:text-gray-500" width="48" height="48"></iconify-icon>
                        </div>
                    </template>
                    <div class="absolute inset-0 rounded-full bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                        <iconify-icon icon="lucide:camera" class="text-white" width="24" height="24"></iconify-icon>
                    </div>
                </div>
            </label>
            <input
                type="file"
                name="{{ $name }}"
                id="{{ $id }}"
                {{ $multiple ? 'multiple' : '' }}
                @if($accept) accept="{{ $accept }}" @endif
                {{ $attributes->whereStartsWith('wire:') }}
                @change="updatePreview"
                class="form-control-file"
            >
            @if($existingAttachment && $removeCheckboxLabel)
                <label class="flex items-center">
                    <input type="checkbox" name="{{ $removeCheckboxName }}" id="{{ $removeCheckboxName }}"
                        class="form-checkbox mr-2">
                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ $removeCheckboxLabel }}</span>
                </label>
            @endif
        </div>
    @else
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
    @endif
    @if($hint)
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $hint }}</p>
    @endif
    @error($name)
        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror
    {{ $slot }}
</div>
