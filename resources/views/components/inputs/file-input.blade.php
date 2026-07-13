@props([
    'label' => __('File'),
    'name' => 'file',
    'id' => null,
    'multiple' => false,
    'existingAttachment' => null,
    'existingAltText' => '',
    'removeCheckboxName' => 'remove_featured_image',
    'removeCheckboxLabel' => null,
    'removeUrl' => null,
    'removeOptionKey' => null,
    'removeConfirmTitle' => null,
    'removeConfirmMessage' => null,
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
    $hasRemoveAction = $existingAttachment && $removeUrl && $removeOptionKey;
    $removeModalId = 'removeImage_' . str_replace('-', '_', $id);
@endphp

<div class="space-y-1">
    @if($label)
        <label for="{{ $id }}" class="form-label">{{ $label }}</label>
    @endif
    @if($avatarStyle)
        <div
            x-data="{
                originalPreview: '{{ $existingAttachment ?? '' }}',
                preview: '{{ $existingAttachment ?? '' }}',
                blobUrl: null,
                hasNewSelection: false,
                {{ $hasRemoveAction ? $removeModalId . 'Open: false, removing: false,' : '' }}
                updatePreview(event) {
                    const file = event.target.files && event.target.files[0];
                    if (this.blobUrl) {
                        URL.revokeObjectURL(this.blobUrl);
                        this.blobUrl = null;
                    }
                    if (file) {
                        this.blobUrl = URL.createObjectURL(file);
                        this.preview = this.blobUrl;
                        this.hasNewSelection = true;
                    } else {
                        this.preview = this.originalPreview;
                        this.hasNewSelection = false;
                    }
                },
                clearSelection() {
                    const input = this.$refs.fileInput;
                    if (input) {
                        input.value = '';
                    }
                    if (this.blobUrl) {
                        URL.revokeObjectURL(this.blobUrl);
                        this.blobUrl = null;
                    }
                    this.preview = this.originalPreview;
                    this.hasNewSelection = false;
                },
                @if($hasRemoveAction)
                async confirmRemove() {
                    this.removing = true;
                    try {
                        const response = await fetch('{{ $removeUrl }}', {
                            method: 'DELETE',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({ option_key: '{{ $removeOptionKey }}' })
                        });
                        const data = await response.json();
                        if (data.success) {
                            window.location.reload();
                        }
                    } catch (error) {
                        console.error('Error removing image:', error);
                    } finally {
                        this.removing = false;
                        this.{{ $removeModalId }}Open = false;
                    }
                }
                @endif
            }"
            class="flex flex-col items-center gap-3 {{ $selectedImageClass ?? '' }}"
        >
            <div class="relative">
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
                <button
                    type="button"
                    x-show="hasNewSelection"
                    x-cloak
                    x-on:click.stop.prevent="clearSelection()"
                    class="absolute -top-1 -right-1 z-10 inline-flex items-center justify-center w-6 h-6 rounded-full bg-red-600 text-white shadow-md ring-2 ring-white dark:ring-gray-800 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-colors"
                    aria-label="{{ __('Remove selected image') }}"
                    title="{{ __('Remove selected image') }}"
                >
                    <iconify-icon icon="lucide:x" width="14" height="14" aria-hidden="true"></iconify-icon>
                </button>
            </div>
            <input
                type="file"
                name="{{ $name }}"
                id="{{ $id }}"
                x-ref="fileInput"
                {{ $multiple ? 'multiple' : '' }}
                @if($accept) accept="{{ $accept }}" @endif
                {{ $attributes->whereStartsWith('wire:') }}
                @change="updatePreview"
                class="form-control-file"
            >
            @if($hasRemoveAction)
                <button
                    type="button"
                    x-on:click="{{ $removeModalId }}Open = true"
                    class="inline-flex items-center gap-1 text-sm text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 transition-colors"
                >
                    <iconify-icon icon="lucide:trash-2" width="14" height="14" aria-hidden="true"></iconify-icon>
                    {{ __('Remove') }}
                </button>

                @include('components.inputs.partials.remove-image-modal', [
                    'modalId' => $removeModalId,
                    'title' => $removeConfirmTitle ?? __('Remove Image'),
                    'message' => $removeConfirmMessage ?? __('Are you sure you want to remove this image? This action cannot be undone.'),
                ])
            @elseif($existingAttachment && $removeCheckboxLabel)
                <label class="flex items-center">
                    <input type="checkbox" name="{{ $removeCheckboxName }}" id="{{ $removeCheckboxName }}"
                        class="form-checkbox mr-2">
                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ $removeCheckboxLabel }}</span>
                </label>
            @endif
        </div>
    @else
        @if ($existingAttachment)
            <div
                @if($hasRemoveAction)
                    x-data="{
                        {{ $removeModalId }}Open: false,
                        removing: false,
                        async confirmRemove() {
                            this.removing = true;
                            try {
                                const response = await fetch('{{ $removeUrl }}', {
                                    method: 'DELETE',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                        'Accept': 'application/json',
                                    },
                                    body: JSON.stringify({ option_key: '{{ $removeOptionKey }}' })
                                });
                                const data = await response.json();
                                if (data.success) {
                                    window.location.reload();
                                }
                            } catch (error) {
                                console.error('Error removing image:', error);
                            } finally {
                                this.removing = false;
                                this.{{ $removeModalId }}Open = false;
                            }
                        }
                    }"
                @endif
                class="mb-4 group/remove {{ $selectedImageClass ?? '' }}"
            >
                <div>
                    <img src="{{ $existingAttachment }}" alt="{{ $existingAltText }}" class="max-h-48 rounded-md">
                </div>
                @if($hasRemoveAction)
                    <div class="mt-2 md:opacity-0 md:group-hover/remove:opacity-100 transition-opacity duration-200">
                        <button
                            type="button"
                            x-on:click="{{ $removeModalId }}Open = true"
                            class="inline-flex items-center gap-1.5 text-sm text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 transition-colors"
                        >
                            <iconify-icon icon="lucide:trash-2" width="15" height="15" aria-hidden="true"></iconify-icon>
                            {{ __('Remove') }}
                        </button>
                    </div>
                @endif

                @if($hasRemoveAction)
                    @include('components.inputs.partials.remove-image-modal', [
                        'modalId' => $removeModalId,
                        'title' => $removeConfirmTitle ?? __('Remove Image'),
                        'message' => $removeConfirmMessage ?? __('Are you sure you want to remove this image? This action cannot be undone.'),
                    ])
                @elseif($removeCheckboxLabel)
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
