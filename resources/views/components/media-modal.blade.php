@props([
    'id' => 'mediaModal',
    'title' => __('Select Media'),
    'multiple' => false,
    'allowedTypes' => 'all', // 'all', 'images', 'videos', 'documents'
    'onSelect' => null,
    'buttonText' => __('Select Media'),
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
<div id="{{ $id }}" class="fixed inset-0 z-50 hidden bg-black/20 backdrop-blur-sm flex items-center justify-center p-2 sm:p-4">
    <div class="bg-white/95 dark:bg-gray-800/95 backdrop-blur-md rounded-lg shadow-2xl border border-white/20 dark:border-gray-700/50 max-w-7xl w-full h-[95vh] sm:h-[90vh] flex flex-col">
        <!-- Modal Header -->
        <div class="flex items-center justify-between p-4 lg:p-6 border-b border-gray-200 dark:border-gray-700 shrink-0">
            <h3 class="text-base lg:text-lg font-semibold text-gray-900 dark:text-white">{{ $title }}</h3>
            <button
                type="button"
                onclick="closeMediaModal('{{ $id }}')"
                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
            >
                <iconify-icon icon="lucide:x" class="text-xl"></iconify-icon>
            </button>
        </div>

        <!-- Modal Content -->
        <div class="flex-1 flex flex-col lg:flex-row overflow-hidden">
            <!-- Left Sidebar - Filters -->
            <div class="w-full lg:w-64 bg-gray-50 dark:bg-gray-900 border-b lg:border-b-0 lg:border-r border-gray-200 dark:border-gray-700 p-4 shrink-0">
                <!-- Mobile/Desktop Layout -->
                <div class="flex flex-col sm:flex-row lg:flex-col gap-3 lg:gap-0">
                    <!-- Upload Section -->
                    <div class="lg:mb-6 sm:w-auto lg:w-full">
                        <button
                            type="button"
                            onclick="triggerFileUpload('{{ $id }}')"
                            class="w-full btn-primary flex items-center justify-center gap-2"
                        >
                            <iconify-icon icon="lucide:upload"></iconify-icon>
                            <span class="hidden sm:inline lg:inline">Upload Files</span>
                            <span class="sm:hidden lg:hidden">Upload</span>
                        </button>
                        <input
                            type="file"
                            id="{{ $id }}_fileInput"
                            class="hidden"
                            {{ $multiple ? 'multiple' : '' }}
                            accept="{{ $allowedTypes === 'images' ? 'image/*' : ($allowedTypes === 'videos' ? 'video/*' : ($allowedTypes === 'audio' ? 'audio/*' : ($allowedTypes === 'documents' ? '.pdf,.doc,.docx,.txt' : '*'))) }}"
                            onchange="handleFileUpload(event, '{{ $id }}')"
                        >
                    </div>

                    <!-- Filter Section -->
                    <div class="flex flex-row sm:flex-row lg:flex-col gap-3 lg:gap-4 flex-1 lg:flex-initial">
                        <div class="flex-1 lg:flex-initial">
                            <label class="hidden lg:block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Filter by Type</label>
                            <select
                                id="{{ $id }}_typeFilter"
                                class="form-control w-full"
                                onchange="filterMediaByType('{{ $id }}', this.value)"
                            >
                                <option value="all">All Files</option>
                                <option value="images">Images</option>
                                <option value="videos">Videos</option>
                                <option value="audio">Audio</option>
                                <option value="documents">Documents</option>
                            </select>
                        </div>

                        <div class="flex-1 lg:flex-initial">
                            <label class="hidden lg:block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Search</label>
                            <input
                                type="text"
                                id="{{ $id }}_searchInput"
                                class="form-control w-full"
                                placeholder="Search files..."
                                oninput="searchMedia('{{ $id }}', this.value)"
                            >
                        </div>
                    </div>

                    <!-- Selected Files Count -->
                    <div id="{{ $id }}_selectedInfo" class="lg:mt-6 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg hidden">
                        <p class="text-sm text-blue-700 dark:text-blue-300">
                            <span id="{{ $id }}_selectedCount">0</span> file(s) selected
                        </p>
                    </div>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="flex-1 flex flex-col min-h-0 lg:min-h-full">
                <!-- Loading State -->
                <div id="{{ $id }}_loading" class="flex-1 flex items-center justify-center">
                    <div class="text-center">
                        <iconify-icon icon="lucide:loader-2" class="text-4xl text-gray-400 animate-spin mb-4"></iconify-icon>
                        <p class="text-gray-500 dark:text-gray-400">Loading media files...</p>
                    </div>
                </div>

                <!-- Media Grid -->
                <div id="{{ $id }}_mediaGrid" class="flex-1 p-4 lg:p-6 overflow-y-auto hidden">
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-5 gap-3 lg:gap-4" id="{{ $id }}_mediaContainer">
                        <!-- Media items will be loaded here -->
                    </div>

                    <!-- Load More Button -->
                    <div id="{{ $id }}_loadMoreSection" class="flex justify-center mt-6 hidden">
                        <button
                            type="button"
                            id="{{ $id }}_loadMoreButton"
                            onclick="loadMoreMedia('{{ $id }}')"
                            class="btn-default px-6 py-2 flex items-center gap-2"
                        >
                            <iconify-icon icon="lucide:chevron-down" class="text-lg"></iconify-icon>
                            Load More (100 items)
                        </button>
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

            <!-- Right Sidebar - Media Details -->
            <div class="hidden lg:flex w-64 bg-white dark:bg-gray-800 border-l border-gray-200 dark:border-gray-700 flex-col shrink-0">
                <!-- No Selection State -->
                <div id="{{ $id }}_noSelection" class="flex-1 flex items-center justify-center p-6">
                    <div class="text-center">
                        <iconify-icon icon="lucide:mouse-pointer-click" class="text-4xl text-gray-300 dark:text-gray-600 mb-3"></iconify-icon>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">Select a file to view details</p>
                    </div>
                </div>

                <!-- Media Details -->
                <div id="{{ $id }}_mediaDetails" class="hidden flex-1 flex flex-col">
                    <!-- Preview Area -->
                    <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                        <div id="{{ $id }}_previewContainer" class="bg-gray-50 dark:bg-gray-900 rounded-lg overflow-hidden relative group">
                            <!-- Preview content will be inserted here -->
                        </div>
                    </div>

                    <!-- File Info -->
                    <div class="flex-1 p-4 overflow-y-auto">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">File Name</label>
                                <p id="{{ $id }}_fileName" class="text-sm text-gray-900 dark:text-white break-all"></p>
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">File Type</label>
                                <p id="{{ $id }}_fileType" class="text-sm text-gray-900 dark:text-white"></p>
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">File Size</label>
                                <p id="{{ $id }}_fileSize" class="text-sm text-gray-900 dark:text-white"></p>
                            </div>

                            <div id="{{ $id }}_imageDimensions" class="hidden">
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Dimensions</label>
                                <p id="{{ $id }}_dimensions" class="text-sm text-gray-900 dark:text-white"></p>
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Uploaded</label>
                                <p id="{{ $id }}_uploadDate" class="text-sm text-gray-900 dark:text-white"></p>
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">URL</label>
                                <div class="flex items-center gap-2">
                                    <input id="{{ $id }}_fileUrl" type="text" readonly class="form-control text-xs flex-1" />
                                    <button type="button" onclick="copyToClipboard('{{ $id }}_fileUrl')" class="btn-default p-2" title="{{ __('Copy URL') }}">
                                        <iconify-icon icon="lucide:copy" class="text-sm"></iconify-icon>
                                    </button>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex gap-2 pt-2">
                                <button type="button"
                                        id="{{ $id }}_downloadButton"
                                        class="flex-1 btn-default flex items-center justify-center gap-2 text-sm"
                                        title="{{ __('Download') }}">
                                    <iconify-icon icon="lucide:download" class="text-sm"></iconify-icon>
                                    <span>{{ __('Download') }}</span>
                                </button>
                                <button type="button"
                                        id="{{ $id }}_fullViewButton"
                                        class="flex-1 btn-default flex items-center justify-center gap-2 text-sm hidden"
                                        title="{{ __('Full View') }}">
                                    <iconify-icon icon="lucide:maximize-2" class="text-sm"></iconify-icon>
                                    <span>{{ __('View') }}</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Selection Actions -->
                    <div class="p-4 border-t border-gray-200 dark:border-gray-700" x-show="false">
                        <!-- Remove the individual select button -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="flex flex-col sm:flex-row items-center justify-between p-4 lg:p-6 border-t border-gray-200 dark:border-gray-700 gap-3 shrink-0">
            <div class="text-sm text-gray-500 dark:text-gray-400 order-2 sm:order-1">
                <span id="{{ $id }}_totalFiles">0</span> files
                <span id="{{ $id }}_filterInfo" class="ml-1 hidden sm:inline"></span>
                <span id="{{ $id }}_paginationInfo" class="ml-2 text-xs hidden sm:inline"></span>
            </div>
            <div class="flex gap-3 w-full sm:w-auto order-1 sm:order-2">
                <button
                    type="button"
                    onclick="closeMediaModal('{{ $id }}')"
                    class="btn-default flex-1 sm:flex-initial"
                >
                    Cancel
                </button>
                <button
                    type="button"
                    id="{{ $id }}_selectButton"
                    onclick="confirmMediaSelection('{{ $id }}')"
                    class="btn-primary flex-1 sm:flex-initial"
                    disabled
                >
                    {{ $multiple ? __('Select Files') : __('Select') }}
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Image Modal for Full View -->
<div id="imageModal" class="fixed inset-0 z-[60] hidden bg-black bg-opacity-75"
    onclick="closeImageModal()">
    <div class="max-w-4xl max-h-[90vh] p-4" onclick="event.stopPropagation()">
        <img id="modalImage" src="" alt="" class="max-w-full max-h-full object-contain">
    </div>
    <button type="button" onclick="closeImageModal()" class="absolute top-4 right-4 text-white hover:text-gray-300">
        <iconify-icon icon="lucide:x" class="text-2xl"></iconify-icon>
    </button>
</div>

<!-- Video Modal for Full View -->
<div id="videoModal" class="fixed inset-0 z-[60] hidden bg-black bg-opacity-75"
    onclick="closeVideoModal()">
    <div class="max-w-6xl max-h-[90vh] p-4" onclick="event.stopPropagation()">
        <video id="modalVideo"
               class="max-w-full max-h-full"
               controls
               preload="metadata"
               style="outline: none;"
               onloadstart="this.volume=0.5">
            <!-- Source will be set dynamically -->
        </video>
    </div>
    <button type="button" onclick="closeVideoModal()" class="absolute top-4 right-4 text-white hover:text-gray-300 z-10">
        <iconify-icon icon="lucide:x" class="text-2xl"></iconify-icon>
    </button>
</div>

<!-- Audio Modal for Full View -->
<div id="audioModal" class="fixed inset-0 z-[60] hidden bg-black bg-opacity-75"
    onclick="closeAudioModal()">
    <div class="max-w-2xl max-h-[90vh] p-4" onclick="event.stopPropagation()">
        <div class="bg-gradient-to-br from-green-100 to-emerald-200 dark:from-green-900 dark:to-emerald-800 rounded-lg p-8 text-center">
            <!-- Audio Visualization -->
            <div class="mb-6">
                <iconify-icon icon="lucide:music" class="text-8xl text-green-600 dark:text-green-300 mb-4"></iconify-icon>
                <h3 id="modalAudioTitle" class="text-xl font-semibold text-green-800 dark:text-green-200 mb-2"></h3>
                <p class="text-green-600 dark:text-green-400 text-sm">Audio Player</p>
            </div>

            <!-- Audio Player -->
            <div class="bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm rounded-lg p-4">
                <audio id="modalAudio"
                       class="w-full mb-4"
                       controls
                       preload="metadata"
                       style="height: 40px;"
                       onloadstart="this.volume=0.5">
                    <!-- Source will be set dynamically -->
                </audio>

                <!-- Audio Info -->
                <div id="modalAudioInfo" class="text-sm text-green-700 dark:text-green-300 space-y-1">
                    <!-- Audio details will be inserted here -->
                </div>
            </div>
        </div>
    </div>
    <button type="button" onclick="closeAudioModal()" class="absolute top-4 right-4 text-white hover:text-gray-300 z-10">
        <iconify-icon icon="lucide:x" class="text-2xl"></iconify-icon>
    </button>
</div>


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
        allFiles: [],
        currentPage: 1,
        totalPages: 1,
        totalCount: 0,
        isLoading: false,
        hasMorePages: true,
        currentFile: null,
        currentFilters: {
            search: '',
            type: 'all'
        }
    };

    modal.classList.remove('hidden');
    loadMediaFiles(modalId, true);
}

function closeMediaModal(modalId) {
    const modal = document.getElementById(modalId);
    if (!modal) return;

    modal.classList.add('hidden');

    // Reset modal state
    if (window.mediaModalData[modalId]) {
        window.mediaModalData[modalId].selectedFiles = [];
        window.mediaModalData[modalId].allFiles = [];
        window.mediaModalData[modalId].currentPage = 1;
        window.mediaModalData[modalId].hasMorePages = true;
        window.mediaModalData[modalId].currentFile = null;
        updateSelectedInfo(modalId);

        // Reset details sidebar
        document.getElementById(`${modalId}_noSelection`).classList.remove('hidden');
        document.getElementById(`${modalId}_mediaDetails`).classList.add('hidden');
    }
}

async function loadMediaFiles(modalId, isInitialLoad = false) {
    const modalData = window.mediaModalData[modalId];
    const loadingEl = document.getElementById(`${modalId}_loading`);
    const gridEl = document.getElementById(`${modalId}_mediaGrid`);
    const emptyEl = document.getElementById(`${modalId}_emptyState`);

    // Prevent multiple simultaneous requests
    if (modalData.isLoading) return;
    modalData.isLoading = true;

    // Show loading state only for initial load
    if (isInitialLoad) {
        loadingEl.classList.remove('hidden');
        gridEl.classList.add('hidden');
        emptyEl.classList.add('hidden');
    }

    try {
        const params = new URLSearchParams({
            page: modalData.currentPage.toString(),
            per_page: 100,
            search: modalData.currentFilters.search,
            type: modalData.currentFilters.type === 'all' ? '' : modalData.currentFilters.type,
            sort: 'created_at',
            direction: 'desc'
        });

        const response = await fetch(`/admin/media/api?${params}`);
        const data = await response.json();

        if (data.success) {
            // Store the total count for reference first
            modalData.totalCount = data.pagination.total;
            modalData.totalPages = data.pagination.last_page;
            modalData.hasMorePages = data.pagination.has_more_pages;

            if (isInitialLoad) {
                modalData.allFiles = data.media;
                renderMediaFiles(modalId, data.media, isInitialLoad);
            } else {
                modalData.allFiles = [...modalData.allFiles, ...data.media];
                renderMediaFiles(modalId, data.media, isInitialLoad); // Pass only new files for rendering
                // Update load more button after loading more items
                updateLoadMoreButton(modalId);
            }

            updateTotalFilesCount(modalId, data.pagination.total);

            if (modalData.allFiles.length > 0) {
                if (isInitialLoad) {
                    loadingEl.classList.add('hidden');
                    gridEl.classList.remove('hidden');
                    // Show load more button if there are more pages
                    updateLoadMoreButton(modalId);
                }
            } else {
                if (isInitialLoad) {
                    loadingEl.classList.add('hidden');
                    emptyEl.classList.remove('hidden');
                }
            }
        } else {
            throw new Error(data.message || 'Failed to load media');
        }
    } catch (error) {
        console.error('Error loading media files:', error);
        if (isInitialLoad) {
            loadingEl.classList.add('hidden');
            emptyEl.classList.remove('hidden');
        }
    } finally {
        modalData.isLoading = false;
    }
}

function renderMediaFiles(modalId, files, isInitialLoad = false) {
    const container = document.getElementById(`${modalId}_mediaContainer`);
    const modalData = window.mediaModalData[modalId];

    if (isInitialLoad) {
        container.innerHTML = '';
    }

    // For both initial load and pagination, render the provided files
    files.forEach(file => {
        // Check if this file is already rendered to avoid duplicates
        if (!isInitialLoad) {
            const existingItem = container.querySelector(`[data-file-id="${file.id}"]`);
            if (existingItem) {
                console.log('File already exists, skipping:', file.id);
                return;
            }
        }

        // Filter by allowed types
        if (modalData.allowedTypes !== 'all') {
            const isAllowed = checkFileTypeAllowed(file.mime_type, modalData.allowedTypes);
            if (!isAllowed) return;
        }

        const mediaItem = createMediaItem(modalId, file);
        container.appendChild(mediaItem);
    });

    // Update checkbox states after rendering
    if (!isInitialLoad) {
        updateCheckboxStates(modalId);
    }
}

function createMediaItem(modalId, file) {
    const modalData = window.mediaModalData[modalId];
    const div = document.createElement('div');
    div.className = 'relative group border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden hover:shadow-md transition-all duration-200 cursor-pointer';
    div.dataset.fileId = file.id;
    div.onclick = () => selectMediaFile(modalId, file);

    const isImage = file.mime_type.startsWith('image/');
    const isVideo = file.mime_type.startsWith('video/');
    const isAudio = file.mime_type.startsWith('audio/');
    const isPdf = file.mime_type.includes('pdf');

    let thumbnailHtml = '';
    if (isImage) {
        const imgSrc = file.thumbnail_url || file.url;
        thumbnailHtml = `<img src="${imgSrc}" alt="${file.name}" class="w-full h-32 object-cover" loading="lazy">`;
    } else if (isVideo) {
        thumbnailHtml = `
            <div class="w-full h-32 bg-gradient-to-br from-purple-100 to-purple-200 dark:from-purple-900 dark:to-purple-800 flex items-center justify-center">
                <iconify-icon icon="lucide:video" class="text-3xl text-purple-600 dark:text-purple-300"></iconify-icon>
            </div>`;
    } else if (isAudio) {
        thumbnailHtml = `
            <div class="w-full h-32 bg-gradient-to-br from-green-100 to-emerald-200 dark:from-green-900 dark:to-emerald-800 flex items-center justify-center">
                <iconify-icon icon="lucide:music" class="text-3xl text-green-600 dark:text-green-300"></iconify-icon>
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
        ${modalData.multiple ? `
            <div class="absolute top-2 left-2">
                <input type="checkbox"
                       class="form-checkbox media-checkbox w-4 h-4 text-blue-600 bg-white border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                       data-file-id="${file.id}"
                       onchange="toggleFileSelection(event, '${modalId}', ${file.id})"
                       onclick="event.stopPropagation()">
            </div>
        ` : ''}
    `;

    return div;
}

function selectMediaFile(modalId, file) {
    const modalData = window.mediaModalData[modalId];

    // Update current file and show details
    modalData.currentFile = file;
    showMediaDetails(modalId, file);

    // Update visual selection state
    updateMediaItemSelection(modalId);

    // For single selection, automatically add to selectedFiles when viewing
    if (!modalData.multiple) {
        modalData.selectedFiles = [file];
        updateSelectedInfo(modalId);
    }
}

function toggleFileSelection(event, modalId, fileId) {
    const modalData = window.mediaModalData[modalId];
    const checkbox = event.target;

    // Find the file object from allFiles
    const file = modalData.allFiles.find(f => f.id == fileId);
    if (!file) return;

    if (checkbox.checked) {
        // Add to selection if not already selected
        const isAlreadySelected = modalData.selectedFiles.some(f => f.id === file.id);
        if (!isAlreadySelected) {
            modalData.selectedFiles.push(file);
        }
    } else {
        // Remove from selection
        modalData.selectedFiles = modalData.selectedFiles.filter(f => f.id !== file.id);
    }

    updateSelectedInfo(modalId);
}

function updateCheckboxStates(modalId) {
    const modalData = window.mediaModalData[modalId];

    // Update checkbox states to match selectedFiles
    document.querySelectorAll('.media-checkbox').forEach(checkbox => {
        const fileId = checkbox.dataset.fileId;
        const isSelected = modalData.selectedFiles.some(f => f.id == fileId);
        checkbox.checked = isSelected;
    });
}

function showMediaDetails(modalId, file) {
    const noSelectionEl = document.getElementById(`${modalId}_noSelection`);
    const detailsEl = document.getElementById(`${modalId}_mediaDetails`);
    const previewContainer = document.getElementById(`${modalId}_previewContainer`);

    // Hide no selection state and show details
    noSelectionEl.classList.add('hidden');
    detailsEl.classList.remove('hidden');
    detailsEl.classList.add('flex', 'flex-col');

    // Update file information
    document.getElementById(`${modalId}_fileName`).textContent = file.name;
    document.getElementById(`${modalId}_fileType`).textContent = file.extension?.toUpperCase() || 'Unknown';
    document.getElementById(`${modalId}_fileSize`).textContent = file.human_readable_size || '0 KB';
    document.getElementById(`${modalId}_fileUrl`).value = file.url;

    // Update upload date
    const uploadDate = file.created_at ? new Date(file.created_at).toLocaleDateString() : 'Unknown';
    document.getElementById(`${modalId}_uploadDate`).textContent = uploadDate;

    // Show/hide dimensions for images, videos, and audio duration
    const dimensionsEl = document.getElementById(`${modalId}_imageDimensions`);
    const isImage = file.mime_type.startsWith('image/');
    const isVideo = file.mime_type.startsWith('video/');
    const isAudio = file.mime_type.startsWith('audio/');

    if (isImage && (file.width || file.height)) {
        document.getElementById(`${modalId}_dimensions`).textContent = `${file.width || 0} × ${file.height || 0} pixels`;
        dimensionsEl.classList.remove('hidden');
    } else if (isVideo && (file.width || file.height)) {
        document.getElementById(`${modalId}_dimensions`).textContent = `${file.width || 0} × ${file.height || 0} resolution`;
        dimensionsEl.classList.remove('hidden');
    } else if (isAudio && file.duration) {
        const duration = formatAudioDuration(file.duration);
        document.getElementById(`${modalId}_dimensions`).textContent = `Duration: ${duration}`;
        dimensionsEl.classList.remove('hidden');
    } else {
        dimensionsEl.classList.add('hidden');
    }

    // Generate preview
    generateMediaPreview(modalId, file, previewContainer);

    // Setup action buttons
    setupActionButtons(modalId, file);
}

function generateMediaPreview(modalId, file, container) {
    const isImage = file.mime_type.startsWith('image/');
    const isVideo = file.mime_type.startsWith('video/');
    const isAudio = file.mime_type.startsWith('audio/');
    const isPdf = file.mime_type.includes('pdf');

    if (isImage) {
        container.innerHTML = `
            <img src="${file.thumbnail_url || file.url}"
                 alt="${file.name}"
                 class="w-full h-48 object-contain bg-gray-100 dark:bg-gray-800 cursor-pointer"
                 loading="lazy"
                 onclick="openImageModal('${file.url}', '${file.name}')">
            <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-colors duration-200 flex items-center justify-center opacity-0 group-hover:opacity-100">
                <button onclick="openImageModal('${file.url}', '${file.name}')"
                        class="p-2 bg-white/90 backdrop-blur-sm rounded-md text-gray-700 hover:bg-white transition-colors shadow-lg"
                        title="View Full Size">
                    <iconify-icon icon="lucide:maximize-2" class="text-lg"></iconify-icon>
                </button>
            </div>
        `;
    } else if (isVideo) {
        container.innerHTML = `
            <div class="w-full h-48 bg-black rounded-lg overflow-hidden relative">
                <video
                    class="w-full h-full object-contain"
                    controls
                    preload="metadata"
                    style="background: linear-gradient(135deg, rgb(147 51 234 / 0.1) 0%, rgb(147 51 234 / 0.2) 100%)"
                    onloadstart="this.volume=0.5"
                >
                    <source src="${file.url}" type="${file.mime_type}">
                    Your browser does not support the video tag.
                </video>
                <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-colors duration-200 flex items-center justify-center opacity-0 group-hover:opacity-100 pointer-events-none">
                    <div class="p-2 bg-white/90 backdrop-blur-sm rounded-md text-gray-700 shadow-lg">
                        <iconify-icon icon="lucide:video" class="text-lg"></iconify-icon>
                    </div>
                </div>
            </div>
        `;
    } else if (isAudio) {
        container.innerHTML = `
            <div class="w-full h-48 bg-gradient-to-br from-green-100 to-emerald-200 dark:from-green-900 dark:to-emerald-800 rounded-lg overflow-hidden relative flex flex-col">
                <!-- Audio Waveform Visual -->
                <div class="flex-1 flex items-center justify-center p-4">
                    <div class="text-center">
                        <iconify-icon icon="lucide:music" class="text-4xl text-green-600 dark:text-green-300 mb-3"></iconify-icon>
                        <p class="text-sm font-medium text-green-700 dark:text-green-300 truncate" title="${file.name}">
                            ${file.name}
                        </p>
                    </div>
                </div>
                <!-- Audio Player -->
                <div class="p-3 bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm">
                    <audio
                        class="w-full"
                        controls
                        preload="metadata"
                        style="height: 32px;"
                        onloadstart="this.volume=0.5"
                    >
                        <source src="${file.url}" type="${file.mime_type}">
                        Your browser does not support the audio tag.
                    </audio>
                </div>
                <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-colors duration-200 flex items-center justify-center opacity-0 group-hover:opacity-100 pointer-events-none">
                    <div class="p-2 bg-white/90 backdrop-blur-sm rounded-md text-gray-700 shadow-lg">
                        <iconify-icon icon="lucide:headphones" class="text-lg"></iconify-icon>
                    </div>
                </div>
            </div>
        `;
    } else if (isPdf) {
        container.innerHTML = `
            <div class="w-full h-48 bg-gradient-to-br from-red-100 to-red-200 dark:from-red-900 dark:to-red-800 flex items-center justify-center">
                <iconify-icon icon="lucide:file-text" class="text-6xl text-red-600 dark:text-red-300"></iconify-icon>
            </div>
        `;
    } else {
        container.innerHTML = `
            <div class="w-full h-48 bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-600 flex items-center justify-center">
                <iconify-icon icon="lucide:file" class="text-6xl text-gray-600 dark:text-gray-300"></iconify-icon>
            </div>
        `;
    }
}

function setupActionButtons(modalId, file) {
    const downloadButton = document.getElementById(`${modalId}_downloadButton`);
    const fullViewButton = document.getElementById(`${modalId}_fullViewButton`);
    const isImage = file.mime_type.startsWith('image/');
    const isVideo = file.mime_type.startsWith('video/');
    const isAudio = file.mime_type.startsWith('audio/');

    // Setup download button
    downloadButton.onclick = () => {
        const link = document.createElement('a');
        link.href = file.url;
        link.download = file.name;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    };

    // Setup full view button (for images, videos, and audio)
    if (isImage) {
        fullViewButton.classList.remove('hidden');
        fullViewButton.onclick = () => openImageModal(file.url, file.name);
        // Reset button text for images
        fullViewButton.innerHTML = `
            <iconify-icon icon="lucide:maximize-2" class="text-sm"></iconify-icon>
            <span>{{ __('View') }}</span>
        `;
    } else if (isVideo) {
        fullViewButton.classList.remove('hidden');
        fullViewButton.onclick = () => openVideoModal(file.url, file.name);
        // Update button text and icon for video
        fullViewButton.innerHTML = `
            <iconify-icon icon="lucide:play" class="text-sm"></iconify-icon>
            <span>{{ __('Play') }}</span>
        `;
    } else if (isAudio) {
        fullViewButton.classList.remove('hidden');
        fullViewButton.onclick = () => openAudioModal(file.url, file.name, file);
        // Update button text and icon for audio
        fullViewButton.innerHTML = `
            <iconify-icon icon="lucide:headphones" class="text-sm"></iconify-icon>
            <span>{{ __('Listen') }}</span>
        `;
    } else {
        fullViewButton.classList.add('hidden');
        // Reset button text for other file types
        fullViewButton.innerHTML = `
            <iconify-icon icon="lucide:maximize-2" class="text-sm"></iconify-icon>
            <span>{{ __('View') }}</span>
        `;
    }
}

function updateMediaItemSelection(modalId) {
    const modalData = window.mediaModalData[modalId];

    // Remove previous active states
    document.querySelectorAll(`[data-file-id]`).forEach(item => {
        item.classList.remove('ring-2', 'ring-blue-500', 'bg-blue-50', 'dark:bg-blue-900/20');
    });

    // Add active state to current file
    if (modalData.currentFile) {
        const activeItem = document.querySelector(`[data-file-id="${modalData.currentFile.id}"]`);
        if (activeItem) {
            activeItem.classList.add('ring-2', 'ring-blue-500', 'bg-blue-50', 'dark:bg-blue-900/20');
        }
    }
}

function copyToClipboard(inputId) {
    const input = document.getElementById(inputId);
    if (input) {
        input.select();
        document.execCommand('copy');

        // Show feedback
        if (window.showToast) {
            window.showToast('success', 'Copied', 'URL copied to clipboard');
        }
    }
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
    const filterInfoEl = document.getElementById(`${modalId}_filterInfo`);
    const paginationInfoEl = document.getElementById(`${modalId}_paginationInfo`);
    const modalData = window.mediaModalData[modalId];

    if (totalFilesEl) {
        totalFilesEl.textContent = count;
    }

    if (filterInfoEl && modalData) {
        const hasSearch = modalData.currentFilters.search.length > 0;
        const hasTypeFilter = modalData.currentFilters.type !== 'all';

        if (hasSearch || hasTypeFilter) {
            let filterText = 'matching';
            if (hasSearch && hasTypeFilter) {
                filterText += ` "${modalData.currentFilters.search}" in ${modalData.currentFilters.type}`;
            } else if (hasSearch) {
                filterText += ` "${modalData.currentFilters.search}"`;
            } else if (hasTypeFilter) {
                filterText += ` ${modalData.currentFilters.type}`;
            }
            filterInfoEl.textContent = filterText;
        } else {
            filterInfoEl.textContent = 'available';
        }
    }

    if (paginationInfoEl && modalData) {
        const loadedCount = modalData.allFiles.length;
        if (modalData.hasMorePages && loadedCount < count) {
            paginationInfoEl.textContent = `• ${loadedCount} loaded of ${count} total`;
        } else if (loadedCount >= count && count > 100) {
            paginationInfoEl.textContent = '• all loaded';
        } else {
            paginationInfoEl.textContent = '';
        }
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

function loadMoreMedia(modalId) {
    console.log('loadMoreMedia', modalId);
    const modalData = window.mediaModalData[modalId];

    if (!modalData.hasMorePages || modalData.isLoading) return;

    modalData.currentPage++;
    loadMediaFiles(modalId, false);
}

function updateLoadMoreButton(modalId) {
    const modalData = window.mediaModalData[modalId];
    const loadMoreSection = document.getElementById(`${modalId}_loadMoreSection`);
    const loadMoreButton = document.getElementById(`${modalId}_loadMoreButton`);

    if (!loadMoreSection || !loadMoreButton) return;

    if (modalData.hasMorePages) {
        loadMoreSection.classList.remove('hidden');
        loadMoreButton.disabled = false;

        // Update button text to show remaining items
        const remainingItems = modalData.totalCount - modalData.allFiles.length;
        const itemsToLoad = Math.min(100, remainingItems);
        loadMoreButton.innerHTML = `
            <iconify-icon icon="lucide:chevron-down" class="text-lg"></iconify-icon>
            Load More (${itemsToLoad} items)
        `;
    } else {
        loadMoreSection.classList.add('hidden');
    }
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

        // Check if the response is ok (status 200-299)
        if (!response.ok) {
            const errorData = await response.json().catch(() => ({}));
            throw new Error(errorData.message || `HTTP ${response.status}: ${response.statusText}`);
        }

        const data = await response.json();

        if (data.success) {
            // Reload media files
            loadMediaFiles(modalId, true);

            // Show success message
            if (window.showToast) {
                window.showToast('success', 'Success', 'Files uploaded successfully');
            }
        } else {
            // Handle different types of errors
            let errorMessage = data.message || 'Upload failed';
            
            // Check for validation errors
            if (data.errors) {
                const validationErrors = Object.values(data.errors).flat();
                errorMessage = validationErrors.join(', ');
            }
            
            // Show the specific error message from the API
            if (window.showToast) {
                window.showToast('error', 'Upload Failed', errorMessage);
            } else {
                alert(errorMessage);
            }
            return;
        }
    } catch (error) {
        console.error('Upload error:', error);
        
        // Try to extract error message from response if it's a fetch error
        let errorMessage = 'Failed to upload files';
        if (error.message && error.message !== 'Failed to fetch') {
            errorMessage = error.message;
        }
        
        if (window.showToast) {
            window.showToast('error', 'Upload Error', errorMessage);
        } else {
            alert(errorMessage);
        }
    }

    // Reset file input
    event.target.value = '';
}

function setupInfiniteScroll(modalId) {
    const scrollContainer = document.getElementById(`${modalId}_mediaGrid`);
    const modalData = window.mediaModalData[modalId];

    // Remove any existing scroll listeners
    scrollContainer.removeEventListener('scroll', modalData.scrollHandler);

    // Create the scroll handler
    modalData.scrollHandler = function() {
        const scrollTop = this.scrollTop;
        const scrollHeight = this.scrollHeight;
        const clientHeight = this.clientHeight;

        console.log('Scroll detected:', { scrollTop, scrollHeight, clientHeight, nearBottom: scrollTop + clientHeight >= scrollHeight - 100 });

        // Check if we're near the bottom (within 100px)
        if (scrollTop + clientHeight >= scrollHeight - 100) {
            console.log('Near bottom! hasMorePages:', modalData.hasMorePages, 'isLoading:', modalData.isLoading);
            if (modalData.hasMorePages && !modalData.isLoading) {
                console.log('Loading next page:', modalData.currentPage + 1);
                modalData.currentPage++;
                loadMediaFiles(modalId, false);
            }
        }
    };

    scrollContainer.addEventListener('scroll', modalData.scrollHandler);
}

function showLoadingIndicator(modalId) {
    const container = document.getElementById(`${modalId}_mediaContainer`);
    let loadingIndicator = document.getElementById(`${modalId}_loadingMore`);

    if (!loadingIndicator) {
        loadingIndicator = document.createElement('div');
        loadingIndicator.id = `${modalId}_loadingMore`;
        loadingIndicator.className = 'col-span-full flex items-center justify-center py-8';
        loadingIndicator.innerHTML = `
            <div class="text-center">
                <iconify-icon icon="lucide:loader-2" class="text-2xl text-gray-400 animate-spin mb-2"></iconify-icon>
                <p class="text-sm text-gray-500 dark:text-gray-400">Loading more files...</p>
            </div>
        `;
        container.appendChild(loadingIndicator);
    }

    loadingIndicator.classList.remove('hidden');
}

function hideLoadingIndicator(modalId) {
    const loadingIndicator = document.getElementById(`${modalId}_loadingMore`);
    if (loadingIndicator) {
        loadingIndicator.classList.add('hidden');
    }
}

function filterMediaByType(modalId, type) {
    const modalData = window.mediaModalData[modalId];
    modalData.currentFilters.type = type;
    modalData.currentPage = 1;
    modalData.hasMorePages = true;
    modalData.allFiles = [];

    loadMediaFiles(modalId, true);
}

function searchMedia(modalId, searchTerm) {
    const modalData = window.mediaModalData[modalId];
    modalData.currentFilters.search = searchTerm.trim();
    modalData.currentPage = 1;
    modalData.hasMorePages = true;
    modalData.allFiles = [];

    // Debounce the search to avoid too many API calls
    clearTimeout(modalData.searchTimeout);
    modalData.searchTimeout = setTimeout(() => {
        loadMediaFiles(modalId, true);
    }, 300);
}

function checkFileTypeAllowed(mimeType, allowedType) {
    switch (allowedType) {
        case 'images':
            return mimeType.startsWith('image/');
        case 'videos':
            return mimeType.startsWith('video/');
        case 'audio':
            return mimeType.startsWith('audio/');
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

// Image modal functions
function openImageModal(src, alt) {
    const modal = document.getElementById('imageModal');
    const img = document.getElementById('modalImage');
    img.src = src;
    img.alt = alt;
    modal.classList.remove('hidden');
    modal.classList.add('flex', 'items-center', 'justify-center');
}

function closeImageModal() {
    const modal = document.getElementById('imageModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex', 'items-center', 'justify-center');
}

// Video modal functions
function openVideoModal(src, name) {
    const modal = document.getElementById('videoModal');
    const video = document.getElementById('modalVideo');

    // Clear any existing sources
    video.innerHTML = '';

    // Add the video source
    const source = document.createElement('source');
    source.src = src;
    source.type = getVideoMimeType(src);
    video.appendChild(source);

    // Set video attributes
    video.load(); // Reload the video element with new source

    modal.classList.remove('hidden');
    modal.classList.add('flex', 'items-center', 'justify-center');
}

function closeVideoModal() {
    const modal = document.getElementById('videoModal');
    const video = document.getElementById('modalVideo');

    // Pause video when closing modal
    video.pause();
    video.currentTime = 0;

    modal.classList.add('hidden');
    modal.classList.remove('flex', 'items-center', 'justify-center');
}

function getVideoMimeType(url) {
    const extension = url.split('.').pop().toLowerCase();
    const mimeTypes = {
        'mp4': 'video/mp4',
        'webm': 'video/webm',
        'ogg': 'video/ogg',
        'mov': 'video/quicktime',
        'avi': 'video/x-msvideo',
        'wmv': 'video/x-ms-wmv',
        'flv': 'video/x-flv',
        'm4v': 'video/x-m4v'
    };
    return mimeTypes[extension] || 'video/mp4';
}

// Audio modal functions
function openAudioModal(src, name, file) {
    const modal = document.getElementById('audioModal');
    const audio = document.getElementById('modalAudio');
    const title = document.getElementById('modalAudioTitle');
    const info = document.getElementById('modalAudioInfo');

    // Set audio title
    title.textContent = name;

    // Clear any existing sources
    audio.innerHTML = '';

    // Add the audio source
    const source = document.createElement('source');
    source.src = src;
    source.type = getAudioMimeType(src);
    audio.appendChild(source);

    // Set audio info
    let infoHtml = '';
    if (file.human_readable_size) {
        infoHtml += `<div>Size: ${file.human_readable_size}</div>`;
    }
    if (file.duration) {
        infoHtml += `<div>Duration: ${formatAudioDuration(file.duration)}</div>`;
    }
    if (file.extension) {
        infoHtml += `<div>Format: ${file.extension.toUpperCase()}</div>`;
    }
    info.innerHTML = infoHtml;

    // Set audio attributes and load
    audio.load(); // Reload the audio element with new source

    modal.classList.remove('hidden');
    modal.classList.add('flex', 'items-center', 'justify-center');
}

function closeAudioModal() {
    const modal = document.getElementById('audioModal');
    const audio = document.getElementById('modalAudio');

    // Pause audio when closing modal
    audio.pause();
    audio.currentTime = 0;

    modal.classList.add('hidden');
    modal.classList.remove('flex', 'items-center', 'justify-center');
}

function getAudioMimeType(url) {
    const extension = url.split('.').pop().toLowerCase();
    const mimeTypes = {
        'mp3': 'audio/mpeg',
        'wav': 'audio/wav',
        'ogg': 'audio/ogg',
        'aac': 'audio/aac',
        'flac': 'audio/flac',
        'm4a': 'audio/mp4',
        'webm': 'audio/webm',
        'wma': 'audio/x-ms-wma'
    };
    return mimeTypes[extension] || 'audio/mpeg';
}

function formatAudioDuration(seconds) {
    if (!seconds || isNaN(seconds)) return 'Unknown';

    const hours = Math.floor(seconds / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    const remainingSeconds = Math.floor(seconds % 60);

    if (hours > 0) {
        return `${hours}:${minutes.toString().padStart(2, '0')}:${remainingSeconds.toString().padStart(2, '0')}`;
    } else {
        return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`;
    }
}

// Close modal on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        // Close audio modal first if open
        const audioModal = document.getElementById('audioModal');
        if (audioModal && !audioModal.classList.contains('hidden')) {
            closeAudioModal();
            return;
        }

        // Close video modal if open
        const videoModal = document.getElementById('videoModal');
        if (videoModal && !videoModal.classList.contains('hidden')) {
            closeVideoModal();
            return;
        }

        // Close image modal if open
        const imageModal = document.getElementById('imageModal');
        if (imageModal && !imageModal.classList.contains('hidden')) {
            closeImageModal();
            return;
        }

        // Then close media modals
        const openModals = document.querySelectorAll('[id$="Modal"]:not(.hidden)');
        openModals.forEach(modal => {
            if (modal.id.includes('media') || modal.id.includes('Media')) {
                closeMediaModal(modal.id);
            }
        });
    }
});
</script>
@endpush
