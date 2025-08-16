@props([
    'name' => 'featured_image',
    'label' => 'Featured Image',
    'multiple' => false,
    'allowedTypes' => 'images',
    'existingMedia' => null,
    'existingAltText' => '',
    'removeCheckboxName' => 'remove_featured_image',
    'removeCheckboxLabel' => null,
    'required' => false,
    'height' => '200px',
    'showPreview' => true,
    'showPreviewCircular' => false,
    'buttonText' => null,
    'showClearButton' => false,
    'emptyText' => __('No media selected'),
    'showNoSelection' => false,
])

@php
    $modalId = 'mediaSelector_' . str_replace(['[', ']', '.'], '_', $name);
    $inputId = 'input_' . str_replace(['[', ']', '.'], '_', $name);
    $previewId = 'preview_' . str_replace(['[', ']', '.'], '_', $name);
    // Only pass array data to selectedFiles, not URL strings
    $selectedFiles = [];
    if ($existingMedia && is_array($existingMedia)) {
        $selectedFiles = $existingMedia;
    }
@endphp

<div 
    {{ $attributes->merge(['class' => 'space-y-1']) }} 
    x-data="mediaSelector('{{ $modalId }}', {{ json_encode($selectedFiles) }}, {{ $multiple ? 'true' : 'false' }}, {{ $existingMedia ? 'true' : 'false' }})"
>
    <label class="form-label">
        {{ $label }}
        @if($required)
            <span class="text-red-500">*</span>
        @endif
    </label>

    <!-- Preview Area -->
    @if($showPreview)
        <div id="{{ $previewId }}" class="space-y-3">
            <!-- Improved fallback if no media selected and no existing media -->
             @if($showNoSelection)
            <div x-show="selectedMedia.length === 0 && !showExistingMedia" class="flex flex-col items-center justify-center">
                <div class="w-48 h-48 {{ $showPreviewCircular ? 'rounded-full' : 'rounded-md' }} flex items-center justify-center border border-gray-200 dark:border-gray-700 shadow-sm bg-primary text-white">
                    <span class="text-xl font-semibold text-center leading-tight">
                        {{ $emptyText }}
                    </span>
                </div>
            </div>
            @endif

            <!-- Show existing media if no new media selected -->
            @if($existingMedia)
                <div x-show="selectedMedia.length === 0 && showExistingMedia" class="space-y-3">
                    @if(is_array($existingMedia))
                        @if($multiple)
                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                                @foreach($existingMedia as $media)
                                    <div class="relative group">
                                        <img src="{{ $media['url'] ?? $media }}"
                                             alt="{{ $media['alt'] ?? '' }}"
                                             class="w-full h-32 object-cover rounded-lg border border-gray-200 dark:border-gray-700"
                                             onerror="console.error('Failed to load image:', this.src); this.style.display='none';">
                                        <button type="button"
                                                @click="clearSelectedMedia()"
                                                class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity w-8 h-8">
                                            <iconify-icon icon="lucide:x" class="text-xs"></iconify-icon>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="flex flex-col items-center justify-center">
                                <div class="relative group w-full h-full">
                                    <img src="{{ $existingMedia[0]['url'] ?? $existingMedia[0] }}"
                                         alt="{{ $existingMedia[0]['alt'] ?? '' }}"
                                         class="w-full h-full object-cover border border-gray-200 dark:border-gray-700 shadow-sm {{ $showPreviewCircular ? 'rounded-full' : 'rounded-md' }}"
                                         onerror="console.error('Failed to load image:', this.src); this.style.display='none';">
                                    <button type="button"
                                            @click="clearSelectedMedia()"
                                            class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity w-8 h-8">
                                        <iconify-icon icon="lucide:x" class="text-xs"></iconify-icon>
                                    </button>
                                </div>
                            </div>
                        @endif
                    @else
                        <div class="flex flex-col items-center justify-center">
                            <div class="relative group w-full h-full">
                                <img src="{{ $existingMedia }}"
                                     alt="{{ $existingAltText }}"
                                     class="w-full h-full object-cover border border-gray-200 dark:border-gray-700 shadow-sm {{ $showPreviewCircular ? 'rounded-full' : 'rounded-md' }}"
                                     onerror="console.error('Failed to load existing image:', '{{ $existingMedia }}'); this.style.display='none'; this.nextElementSibling.style.display='block';">
                                <div style="display: none;" class="w-full h-full bg-gray-100 dark:bg-gray-800 {{ $showPreviewCircular ? 'rounded-full' : 'rounded-md' }} border-4 border-gray-200 dark:border-gray-700 flex items-center justify-center">
                                    <span class="text-gray-500 text-sm">Image not found</span>
                                </div>
                                <button type="button"
                                        @click="clearSelectedMedia()"
                                        class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity w-8 h-8">
                                    <iconify-icon icon="lucide:x" class="text-xs"></iconify-icon>
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            <!-- Show newly selected media -->
            <div x-show="selectedMedia.length > 0" class="space-y-3">
                <div x-show="multiple">
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                        <template x-for="(media, index) in selectedMedia" :key="media.id">
                            <div class="relative group">
                                <img :src="media.thumbnail_url || media.url"
                                     :alt="media.name"
                                     class="w-full h-32 object-cover rounded-lg border border-gray-200 dark:border-gray-700">
                                <button type="button"
                                        @click="removeSelectedMedia(media.id)"
                                        class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity w-8 h-8">
                                    <iconify-icon icon="lucide:x" class="text-xs"></iconify-icon>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>
                <div x-show="!multiple">
                    <div class="flex flex-col items-center justify-center">
                        <template x-for="(media, index) in selectedMedia" :key="media.id">
                            <div class="relative group w-full h-full">
                                <img :src="media.thumbnail_url || media.url"
                                     :alt="media.name"
                                     class="'w-full h-full object-cover border border-gray-200 dark:border-gray-700 shadow-sm {{ $showPreviewCircular ? 'rounded-full' : 'rounded-md' }}"
                                >
                                <button type="button"
                                        @click="removeSelectedMedia(media.id)"
                                        class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity w-8 h-8">
                                    <iconify-icon icon="lucide:x" class="text-xs"></iconify-icon>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            @if($removeCheckboxLabel)
                <div class="mt-2" x-show="{{ $existingMedia ? 'true' : 'false' }} && selectedMedia.length === 0">
                    <label class="flex items-center">
                        <input type="checkbox"
                               name="{{ $removeCheckboxName }}"
                               id="{{ $removeCheckboxName }}"
                               class="form-checkbox mr-2">
                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ $removeCheckboxLabel }}</span>
                    </label>
                </div>
            @endif
        </div>
    @endif

    <!-- Action Buttons -->
    <div class="flex gap-2 w-full">
        <button type="button"
                onclick="openMediaModal('{{ $modalId }}', {{ $multiple ? 'true' : 'false' }}, '{{ $allowedTypes }}', 'handleMediaSelection_{{ $modalId }}')"
                class="btn-default flex items-center gap-2 w-full">
            <iconify-icon icon="lucide:image"></iconify-icon>
            {{ $buttonText ? $buttonText : __('Select Image') }}
        </button>

        @if($showClearButton)
        <button type="button"
                @click="clearSelectedMedia()"
                x-show="selectedMedia.length > 0 || {{ $existingMedia ? 'true' : 'false' }}"
                class="btn-secondary flex items-center gap-2">
            <iconify-icon icon="lucide:x"></iconify-icon>
            {{ __('Clear') }}
        </button>
        @endif
    </div>

    <!-- Hidden inputs to store selected media IDs -->
    <template x-for="(media, index) in selectedMedia" :key="media.id">
        <input type="hidden"
               :name="{{ $multiple ? "'{$name}[]'" : "'{$name}'" }}"
               :value="media.id">
    </template>

    <!-- Fallback: If no new media selected but we have existing media, preserve it unless remove checkbox is checked -->
    @if($existingMedia && !is_array($existingMedia))
        <input type="hidden"
               name="{{ $name }}"
               value="{{ $existingMedia }}"
               x-show="selectedMedia.length === 0">
    @endif
</div>

<!-- Include the media modal component -->
<x-media-modal
    :id="$modalId"
    :title="$multiple ? __('Select Media Files') : __('Select Image')"
    :multiple="$multiple"
    :allowedTypes="$allowedTypes"
    buttonText="{{ __('Select') }}"
    buttonClass="hidden"
/>

@push('scripts')
<script>
    // Alpine.js component for media selector
    function mediaSelector(modalId, existingMedia = [], multiple = false, hasExistingMedia = false) {
        return {
            selectedMedia: [],
            modalId: modalId,
            multiple: multiple,
            removeCheckboxName: '{{ $removeCheckboxName }}',
            showExistingMedia: hasExistingMedia,

            init() {
                window[`handleMediaSelection_${modalId}`] = (files) => {
                    if (multiple) {
                        this.selectedMedia = [...files];
                    } else {
                        this.selectedMedia = files.slice(0, 1);
                    }
                    this.showExistingMedia = false;
                    window.dispatchEvent(new CustomEvent('avatar-selected', { detail: this.selectedMedia.length > 0 }));
                };

                if (existingMedia && Array.isArray(existingMedia) && existingMedia.length > 0) {
                    this.selectedMedia = existingMedia;
                }
            },

            removeSelectedMedia(mediaId) {
                this.selectedMedia = this.selectedMedia.filter(media => media.id != mediaId);
                if (this.selectedMedia.length === 0) {
                    this.showExistingMedia = false;
                }
                window.dispatchEvent(new CustomEvent('avatar-selected', { detail: this.selectedMedia.length > 0 }));
            },

            clearSelectedMedia() {
                this.selectedMedia = [];
                this.showExistingMedia = false;
                const removeCheckbox = document.querySelector(`input[name="${this.removeCheckboxName}"]`);
                if (removeCheckbox) {
                    removeCheckbox.checked = true;
                }
                window.dispatchEvent(new CustomEvent('avatar-selected', { detail: false }));
            }
        }
    }
</script>
@endpush
