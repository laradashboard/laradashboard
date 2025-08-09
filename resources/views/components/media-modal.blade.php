@props([
    'id' => 'mediaModal',
    'title' => 'Select Media',
    'multiple' => false,
    'allowedTypes' => 'all', // 'all', 'images', 'videos', 'documents'
    'onSelect' => null,
    'buttonText' => 'Select Media',
    'buttonClass' => 'btn-primary'
])

<!-- Media Modal Button -->
<button 
    type="button" 
    class="{{ $buttonClass }}"
    onclick="openMediaModal('{{ $id }}', {{ $multiple ? 'true' : 'false' }}, '{{ $allowedTypes }}', {{ $onSelect ? "'{$onSelect}'" : 'null' }})"
>
    <iconify-icon icon="lucide:image" class="mr-2"></iconify-icon>
    {{ $buttonText }}
</button>

<!-- Media Modal -->
<div id="{{ $id }}" class="fixed inset-0 z-50 hidden bg-black bg-opacity-50 flex items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-6xl w-full max-h-[90vh] flex flex-col">
        <!-- Modal Header -->
        <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $title }}</h3>
            <button 
                type="button" 
                onclick="closeMediaModal('{{ $id }}')"
                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
            >
                <iconify-icon icon="lucide:x" class="text-xl"></iconify-icon>
            </button>
        </div>

        <!-- Modal Content -->
        <div class="flex-1 flex overflow-hidden">
            <!-- Sidebar -->
            <div class="w-64 bg-gray-50 dark:bg-gray-900 border-r border-gray-200 dark:border-gray-700 p-4">
                <!-- Upload Section -->
                <div class="mb-6">
                    <button 
                        type="button"
                        onclick="triggerFileUpload('{{ $id }}')"
                        class="w-full btn-primary flex items-center justify-center gap-2"
                    >
                        <iconify-icon icon="lucide:upload"></iconify-icon>
                        Upload Files
                    </button>
                    <input 
                        type="file" 
                        id="{{ $id }}_fileInput" 
                        class="hidden" 
                        {{ $multiple ? 'multiple' : '' }}
                        accept="{{ $allowedTypes === 'images' ? 'image/*' : ($allowedTypes === 'videos' ? 'video/*' : ($allowedTypes === 'documents' ? '.pdf,.doc,.docx,.txt' : '*')) }}"
                        onchange="handleFileUpload(event, '{{ $id }}')"
                    >
                </div>

                <!-- Filter Section -->
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Filter by Type</label>
                        <select 
                            id="{{ $id }}_typeFilter" 
                            class="form-control w-full"
                            onchange="filterMediaByType('{{ $id }}', this.value)"
                        >
                            <option value="all">All Files</option>
                            <option value="images">Images</option>
                            <option value="videos">Videos</option>
                            <option value="documents">Documents</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Search</label>
                        <input 
                            type="text" 
                            id="{{ $id }}_searchInput"
                            class="form-control w-full" 
                            placeholder="Search files..."
                            oninput="searchMedia('{{ $id }}', this.value)"
                        >
                    </div>
                </div>

                <!-- Selected Files Info -->
                <div id="{{ $id }}_selectedInfo" class="mt-6 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg hidden">
                    <p class="text-sm text-blue-700 dark:text-blue-300">
                        <span id="{{ $id }}_selectedCount">0</span> file(s) selected
                    </p>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="flex-1 flex flex-col">
                <!-- Loading State -->
                <div id="{{ $id }}_loading" class="flex-1 flex items-center justify-center">
                    <div class="text-center">
                        <iconify-icon icon="lucide:loader-2" class="text-4xl text-gray-400 animate-spin mb-4"></iconify-icon>
                        <p class="text-gray-500 dark:text-gray-400">Loading media files...</p>
                    </div>
                </div>

                <!-- Media Grid -->
                <div id="{{ $id }}_mediaGrid" class="flex-1 p-6 overflow-y-auto hidden">
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4" id="{{ $id }}_mediaContainer">
                        <!-- Media items will be loaded here -->
                    </div>
                </div>

                <!-- Empty State -->
                <div id="{{ $id }}_emptyState" class="flex-1 flex items-center justify-center hidden">
                    <div class="text-center">
                        <iconify-icon icon="lucide:image" class="text-6xl text-gray-300 dark:text-gray-600 mb-4"></iconify-icon>
                        <p class="text-gray-500 dark:text-gray-400 mb-4">No media files found</p>
                        <button 
                            type="button"
                            onclick="triggerFileUpload('{{ $id }}')"
                            class="btn-primary"
                        >
                            Upload Your First File
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="flex items-center justify-between p-6 border-t border-gray-200 dark:border-gray-700">
            <div class="text-sm text-gray-500 dark:text-gray-400">
                <span id="{{ $id }}_totalFiles">0</span> files available
            </div>
            <div class="flex gap-3">
                <button 
                    type="button" 
                    onclick="closeMediaModal('{{ $id }}')"
                    class="btn-secondary"
                >
                    Cancel
                </button>
                <button 
                    type="button" 
                    id="{{ $id }}_selectButton"
                    onclick="confirmMediaSelection('{{ $id }}')"
                    class="btn-primary"
                    disabled
                >
                    Select
                </button>
            </div>
        </div>
    </div>
</div>

@once
@push('scripts')
<script>
// Global media modal functionality
window.mediaModalData = {};

function openMediaModal(modalId, multiple = false, allowedTypes = 'all', onSelectCallback = null) {
    const modal = document.getElementById(modalId);
    if (!modal) return;

    // Initialize modal data
    window.mediaModalData[modalId] = {
        multiple: multiple,
        allowedTypes: allowedTypes,
        onSelectCallback: onSelectCallback,
        selectedFiles: [],
        allFiles: []
    };

    modal.classList.remove('hidden');
    loadMediaFiles(modalId);
}

function closeMediaModal(modalId) {
    const modal = document.getElementById(modalId);
    if (!modal) return;

    modal.classList.add('hidden');
    
    // Reset modal state
    if (window.mediaModalData[modalId]) {
        window.mediaModalData[modalId].selectedFiles = [];
        updateSelectedInfo(modalId);
    }
}

async function loadMediaFiles(modalId) {
    const loadingEl = document.getElementById(`${modalId}_loading`);
    const gridEl = document.getElementById(`${modalId}_mediaGrid`);
    const emptyEl = document.getElementById(`${modalId}_emptyState`);

    // Show loading state
    loadingEl.classList.remove('hidden');
    gridEl.classList.add('hidden');
    emptyEl.classList.add('hidden');

    try {
        const response = await fetch('/admin/media/api');
        const data = await response.json();

        if (data.success && data.media.length > 0) {
            window.mediaModalData[modalId].allFiles = data.media;
            renderMediaFiles(modalId, data.media);
            updateTotalFilesCount(modalId, data.media.length);
            
            loadingEl.classList.add('hidden');
            gridEl.classList.remove('hidden');
        } else {
            loadingEl.classList.add('hidden');
            emptyEl.classList.remove('hidden');
        }
    } catch (error) {
        console.error('Error loading media files:', error);
        loadingEl.classList.add('hidden');
        emptyEl.classList.remove('hidden');
    }
}

function renderMediaFiles(modalId, files) {
    const container = document.getElementById(`${modalId}_mediaContainer`);
    const modalData = window.mediaModalData[modalId];
    
    container.innerHTML = '';

    files.forEach(file => {
        // Filter by allowed types
        if (modalData.allowedTypes !== 'all') {
            const isAllowed = checkFileTypeAllowed(file.mime_type, modalData.allowedTypes);
            if (!isAllowed) return;
        }

        const mediaItem = createMediaItem(modalId, file);
        container.appendChild(mediaItem);
    });
}

function createMediaItem(modalId, file) {
    const div = document.createElement('div');
    div.className = 'relative group border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden hover:shadow-md transition-all duration-200 cursor-pointer';
    div.dataset.fileId = file.id;
    div.onclick = () => toggleFileSelection(modalId, file);

    const isImage = file.mime_type.startsWith('image/');
    const isVideo = file.mime_type.startsWith('video/');
    const isPdf = file.mime_type.includes('pdf');

    let thumbnailHtml = '';
    if (isImage) {
        thumbnailHtml = `<img src="${file.url}" alt="${file.name}" class="w-full h-32 object-cover" loading="lazy">`;
    } else if (isVideo) {
        thumbnailHtml = `
            <div class="w-full h-32 bg-gradient-to-br from-purple-100 to-purple-200 dark:from-purple-900 dark:to-purple-800 flex items-center justify-center">
                <iconify-icon icon="lucide:video" class="text-3xl text-purple-600 dark:text-purple-300"></iconify-icon>
            </div>`;
    } else if (isPdf) {
        thumbnailHtml = `
            <div class="w-full h-32 bg-gradient-to-br from-red-100 to-red-200 dark:from-red-900 dark:to-red-800 flex items-center justify-center">
                <iconify-icon icon="lucide:file-text" class="text-3xl text-red-600 dark:text-red-300"></iconify-icon>
            </div>`;
    } else {
        thumbnailHtml = `
            <div class="w-full h-32 bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-600 flex items-center justify-center">
                <iconify-icon icon="lucide:file" class="text-3xl text-gray-600 dark:text-gray-300"></iconify-icon>
            </div>`;
    }

    div.innerHTML = `
        ${thumbnailHtml}
        <div class="p-3 bg-white dark:bg-gray-800">
            <p class="text-xs font-medium text-gray-700 dark:text-white truncate" title="${file.name}">
                ${file.name}
            </p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                ${file.extension?.toUpperCase() || 'FILE'} • ${file.human_readable_size || '0 KB'}
            </p>
        </div>
        <div class="absolute inset-0 bg-blue-500 bg-opacity-20 opacity-0 transition-opacity duration-200 flex items-center justify-center media-selected-overlay">
            <iconify-icon icon="lucide:check" class="text-2xl text-white bg-blue-500 rounded-full p-1"></iconify-icon>
        </div>
    `;

    return div;
}

function toggleFileSelection(modalId, file) {
    const modalData = window.mediaModalData[modalId];
    const fileElement = document.querySelector(`[data-file-id="${file.id}"]`);
    const overlay = fileElement.querySelector('.media-selected-overlay');
    
    const isSelected = modalData.selectedFiles.some(f => f.id === file.id);
    
    if (isSelected) {
        // Deselect
        modalData.selectedFiles = modalData.selectedFiles.filter(f => f.id !== file.id);
        overlay.classList.add('opacity-0');
        fileElement.classList.remove('ring-2', 'ring-blue-500');
    } else {
        // Select
        if (!modalData.multiple) {
            // Single selection - clear previous selections
            modalData.selectedFiles.forEach(f => {
                const prevElement = document.querySelector(`[data-file-id="${f.id}"]`);
                if (prevElement) {
                    prevElement.querySelector('.media-selected-overlay').classList.add('opacity-0');
                    prevElement.classList.remove('ring-2', 'ring-blue-500');
                }
            });
            modalData.selectedFiles = [];
        }
        
        modalData.selectedFiles.push(file);
        overlay.classList.remove('opacity-0');
        fileElement.classList.add('ring-2', 'ring-blue-500');
    }
    
    updateSelectedInfo(modalId);
}

function updateSelectedInfo(modalId) {
    const modalData = window.mediaModalData[modalId];
    const selectedInfo = document.getElementById(`${modalId}_selectedInfo`);
    const selectedCount = document.getElementById(`${modalId}_selectedCount`);
    const selectButton = document.getElementById(`${modalId}_selectButton`);
    
    const count = modalData.selectedFiles.length;
    
    if (count > 0) {
        selectedInfo.classList.remove('hidden');
        selectedCount.textContent = count;
        selectButton.disabled = false;
    } else {
        selectedInfo.classList.add('hidden');
        selectButton.disabled = true;
    }
}

function updateTotalFilesCount(modalId, count) {
    const totalFilesEl = document.getElementById(`${modalId}_totalFiles`);
    if (totalFilesEl) {
        totalFilesEl.textContent = count;
    }
}

function confirmMediaSelection(modalId) {
    const modalData = window.mediaModalData[modalId];
    
    if (modalData.selectedFiles.length === 0) return;
    
    // Execute callback if provided
    if (modalData.onSelectCallback && typeof window[modalData.onSelectCallback] === 'function') {
        window[modalData.onSelectCallback](modalData.selectedFiles);
    }
    
    // Dispatch custom event
    const event = new CustomEvent('mediaSelected', {
        detail: {
            modalId: modalId,
            files: modalData.selectedFiles,
            multiple: modalData.multiple
        }
    });
    document.dispatchEvent(event);
    
    closeMediaModal(modalId);
}

function triggerFileUpload(modalId) {
    const fileInput = document.getElementById(`${modalId}_fileInput`);
    fileInput.click();
}

async function handleFileUpload(event, modalId) {
    const files = Array.from(event.target.files);
    if (files.length === 0) return;

    const formData = new FormData();
    files.forEach(file => {
        formData.append('files[]', file);
    });

    try {
        const response = await fetch('/admin/media', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });

        const data = await response.json();
        
        if (data.success) {
            // Reload media files
            loadMediaFiles(modalId);
            
            // Show success message
            if (window.showToast) {
                window.showToast('success', 'Success', 'Files uploaded successfully');
            }
        } else {
            throw new Error(data.message || 'Upload failed');
        }
    } catch (error) {
        console.error('Upload error:', error);
        if (window.showToast) {
            window.showToast('error', 'Error', 'Failed to upload files');
        }
    }

    // Reset file input
    event.target.value = '';
}

function filterMediaByType(modalId, type) {
    const modalData = window.mediaModalData[modalId];
    let filteredFiles = modalData.allFiles;

    if (type !== 'all') {
        filteredFiles = modalData.allFiles.filter(file => 
            checkFileTypeAllowed(file.mime_type, type)
        );
    }

    renderMediaFiles(modalId, filteredFiles);
    updateTotalFilesCount(modalId, filteredFiles.length);
}

function searchMedia(modalId, searchTerm) {
    const modalData = window.mediaModalData[modalId];
    let filteredFiles = modalData.allFiles;

    if (searchTerm.trim()) {
        filteredFiles = modalData.allFiles.filter(file => 
            file.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
            file.file_name.toLowerCase().includes(searchTerm.toLowerCase())
        );
    }

    renderMediaFiles(modalId, filteredFiles);
    updateTotalFilesCount(modalId, filteredFiles.length);
}

function checkFileTypeAllowed(mimeType, allowedType) {
    switch (allowedType) {
        case 'images':
            return mimeType.startsWith('image/');
        case 'videos':
            return mimeType.startsWith('video/');
        case 'documents':
            return mimeType.includes('pdf') || 
                   mimeType.includes('document') || 
                   mimeType.includes('text') ||
                   mimeType.includes('msword') ||
                   mimeType.includes('officedocument');
        default:
            return true;
    }
}

// Close modal on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const openModals = document.querySelectorAll('[id$="Modal"]:not(.hidden)');
        openModals.forEach(modal => {
            if (modal.id.includes('media')) {
                closeMediaModal(modal.id);
            }
        });
    }
});
</script>
@endpush
@endonce