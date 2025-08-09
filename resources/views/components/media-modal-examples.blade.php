@extends('backend.layouts.app')

@section('title')
    Media Modal Demo | {{ config('app.name') }}
@endsection

@section('admin-content')
{{-- Example usage of the Media Modal Component --}}

<div class="space-y-6 p-6">
    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Media Modal Examples</h2>
    
    {{-- Basic single image selection --}}
    <div class="space-y-3">
        <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300">Single Image Selection</h3>
        <x-media-modal 
            id="singleImageModal"
            title="Select an Image"
            :multiple="false"
            allowed-types="images"
            on-select="handleSingleImageSelect"
            button-text="Choose Image"
            button-class="btn-primary"
        />
        
        <div id="selectedImagePreview" class="hidden mt-4">
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Selected Image:</p>
            <div class="flex items-center gap-4">
                <img id="previewImage" src="" alt="" class="w-20 h-20 object-cover rounded border">
                <div>
                    <p id="previewImageName" class="font-medium text-gray-900 dark:text-white"></p>
                    <p id="previewImageSize" class="text-sm text-gray-500 dark:text-gray-400"></p>
                </div>
            </div>
        </div>
    </div>

    {{-- Multiple file selection --}}
    <div class="space-y-3">
        <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300">Multiple Files Selection</h3>
        <x-media-modal 
            id="multipleFilesModal"
            title="Select Multiple Files"
            :multiple="true"
            allowed-types="all"
            on-select="handleMultipleFilesSelect"
            button-text="Choose Files"
            button-class="btn-secondary"
        />
        
        <div id="selectedFilesPreview" class="hidden mt-4">
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Selected Files:</p>
            <div id="selectedFilesList" class="space-y-2"></div>
        </div>
    </div>

    {{-- Documents only --}}
    <div class="space-y-3">
        <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300">Documents Only</h3>
        <x-media-modal 
            id="documentsModal"
            title="Select Document"
            :multiple="false"
            allowed-types="documents"
            on-select="handleDocumentSelect"
            button-text="Choose Document"
            button-class="btn-outline"
        />
        
        <div id="selectedDocumentPreview" class="hidden mt-4">
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Selected Document:</p>
            <div id="documentInfo" class="p-3 bg-gray-50 dark:bg-gray-800 rounded border"></div>
        </div>
    </div>

    {{-- Form integration example --}}
    <div class="space-y-3">
        <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300">Form Integration Example</h3>
        <form class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Featured Image
                </label>
                <input type="hidden" id="featuredImageId" name="featured_image_id" value="">
                <x-media-modal 
                    id="featuredImageModal"
                    title="Select Featured Image"
                    :multiple="false"
                    allowed-types="images"
                    on-select="handleFeaturedImageSelect"
                    button-text="Set Featured Image"
                    button-class="btn-primary"
                />
                <div id="featuredImageDisplay" class="hidden mt-3">
                    <div class="relative inline-block">
                        <img id="featuredImageThumb" src="" alt="" class="w-32 h-32 object-cover rounded border">
                        <button type="button" onclick="removeFeaturedImage()" 
                                class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-red-600">
                            ×
                        </button>
                    </div>
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Gallery Images
                </label>
                <input type="hidden" id="galleryImageIds" name="gallery_image_ids" value="">
                <x-media-modal 
                    id="galleryModal"
                    title="Select Gallery Images"
                    :multiple="true"
                    allowed-types="images"
                    on-select="handleGallerySelect"
                    button-text="Add to Gallery"
                    button-class="btn-secondary"
                />
                <div id="galleryDisplay" class="hidden mt-3">
                    <div id="galleryGrid" class="grid grid-cols-4 gap-2"></div>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
// Single image selection handler
function handleSingleImageSelect(files) {
    if (files.length > 0) {
        const file = files[0];
        const preview = document.getElementById('selectedImagePreview');
        const img = document.getElementById('previewImage');
        const name = document.getElementById('previewImageName');
        const size = document.getElementById('previewImageSize');
        
        img.src = file.url;
        img.alt = file.name;
        name.textContent = file.name;
        size.textContent = file.human_readable_size;
        
        preview.classList.remove('hidden');
    }
}

// Multiple files selection handler
function handleMultipleFilesSelect(files) {
    const preview = document.getElementById('selectedFilesPreview');
    const list = document.getElementById('selectedFilesList');
    
    list.innerHTML = '';
    
    files.forEach(file => {
        const item = document.createElement('div');
        item.className = 'flex items-center gap-3 p-2 bg-gray-50 dark:bg-gray-800 rounded';
        item.innerHTML = `
            <iconify-icon icon="lucide:file" class="text-gray-500"></iconify-icon>
            <div class="flex-1">
                <p class="font-medium text-gray-900 dark:text-white">${file.name}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">${file.human_readable_size}</p>
            </div>
        `;
        list.appendChild(item);
    });
    
    preview.classList.remove('hidden');
}

// Document selection handler
function handleDocumentSelect(files) {
    if (files.length > 0) {
        const file = files[0];
        const preview = document.getElementById('selectedDocumentPreview');
        const info = document.getElementById('documentInfo');
        
        info.innerHTML = `
            <div class="flex items-center gap-3">
                <iconify-icon icon="lucide:file-text" class="text-2xl text-blue-500"></iconify-icon>
                <div>
                    <p class="font-medium text-gray-900 dark:text-white">${file.name}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">${file.extension.toUpperCase()} • ${file.human_readable_size}</p>
                    <a href="${file.url}" target="_blank" class="text-sm text-blue-600 hover:text-blue-800">Download</a>
                </div>
            </div>
        `;
        
        preview.classList.remove('hidden');
    }
}

// Featured image form handler
function handleFeaturedImageSelect(files) {
    if (files.length > 0) {
        const file = files[0];
        const input = document.getElementById('featuredImageId');
        const display = document.getElementById('featuredImageDisplay');
        const thumb = document.getElementById('featuredImageThumb');
        
        input.value = file.id;
        thumb.src = file.url;
        thumb.alt = file.name;
        
        display.classList.remove('hidden');
    }
}

// Remove featured image
function removeFeaturedImage() {
    const input = document.getElementById('featuredImageId');
    const display = document.getElementById('featuredImageDisplay');
    
    input.value = '';
    display.classList.add('hidden');
}

// Gallery selection handler
function handleGallerySelect(files) {
    const input = document.getElementById('galleryImageIds');
    const display = document.getElementById('galleryDisplay');
    const grid = document.getElementById('galleryGrid');
    
    // Store selected file IDs
    const ids = files.map(file => file.id);
    input.value = ids.join(',');
    
    // Clear and rebuild grid
    grid.innerHTML = '';
    
    files.forEach((file, index) => {
        const item = document.createElement('div');
        item.className = 'relative group';
        item.innerHTML = `
            <img src="${file.url}" alt="${file.name}" class="w-full h-20 object-cover rounded border">
            <button type="button" onclick="removeFromGallery(${index})" 
                    class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs hover:bg-red-600 opacity-0 group-hover:opacity-100 transition-opacity">
                ×
            </button>
        `;
        grid.appendChild(item);
    });
    
    display.classList.remove('hidden');
}

// Remove from gallery (simplified - in real app you'd need to track files properly)
function removeFromGallery(index) {
    // This is a simplified example - in a real application, you'd need to properly track and update the selected files
    console.log('Remove gallery item at index:', index);
}
</script>
@endpush
@endsection