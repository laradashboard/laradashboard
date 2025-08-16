<div x-cloak x-show="uploadModalOpen" 
     x-transition.opacity.duration.200ms
     x-trap.inert.noscroll="uploadModalOpen"
     x-on:keydown.esc.window="uploadModalOpen = false"
     x-on:click.self="uploadModalOpen = false"
     class="fixed inset-0 z-50 flex items-center justify-center bg-black/20 p-4 backdrop-blur-md"
     role="dialog"
     aria-modal="true">
    
    <div x-show="uploadModalOpen"
         x-transition:enter="transition ease-out duration-200 delay-100"
         x-transition:enter-start="opacity-0 scale-50"
         x-transition:enter-end="opacity-100 scale-100"
         class="flex max-w-2xl w-full flex-col gap-4 overflow-hidden rounded-md border border-gray-100 dark:border-gray-800 bg-white text-gray-900 dark:bg-gray-700 dark:text-gray-300">
        
        <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4 dark:border-gray-800">
            <h3 class="font-semibold tracking-wide text-gray-700 dark:text-white">
                {{ __('Upload Media Files') }}
            </h3>
            <button x-on:click="uploadModalOpen = false"
                    aria-label="close modal"
                    class="text-gray-400 hover:bg-gray-200 hover:text-gray-700 rounded-md p-1 dark:hover:bg-gray-600 dark:hover:text-white">
                <iconify-icon icon="lucide:x" class="w-5 h-5"></iconify-icon>
            </button>
        </div>
        
        <div class="px-6 pb-6">
            <form id="upload-form" enctype="multipart/form-data">
                @csrf
                <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-8 text-center transition-colors cursor-pointer hover:border-primary hover:bg-primary-50 dark:hover:bg-primary-900/20"
                     id="drop-zone"
                     onclick="document.getElementById('file-input').click()"
                     ondrop="dropHandler(event);"
                     ondragover="dragOverHandler(event);"
                     ondragleave="dragLeaveHandler(event);">
                    <iconify-icon icon="lucide:upload-cloud" class="text-4xl text-gray-400 mb-4 mx-auto"></iconify-icon>
                    <p class="text-gray-600 dark:text-gray-300 mb-4">
                        {{ __('Drag and drop files here, or click to select files') }}
                    </p>
                    <input type="file" 
                           id="file-input" 
                           name="files[]" 
                           multiple 
                           @if(config('app.demo_mode', false))
                           accept="image/*,audio/*,video/*,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.rtf"
                           @else
                           accept="*"
                           @endif
                           class="hidden">
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-2 space-y-1">
                        <p>{{ __('Maximum file size:') }} <span class="font-medium">{{ $uploadLimits['effective_max_filesize_formatted'] }}</span></p>
                        <p>{{ __('Maximum files at once:') }} <span class="font-medium">{{ $uploadLimits['max_file_uploads'] }}</span></p>
                        <p>{{ __('Maximum total upload:') }} <span class="font-medium">{{ $uploadLimits['post_max_size_formatted'] }}</span></p>

                        @if(config('app.demo_mode', false))
                        <p class="text-orange-600 dark:text-orange-400 font-medium">
                            <iconify-icon icon="lucide:info" class="inline w-3 h-3 mr-1"></iconify-icon>
                            {{ __('Demo Mode: Only images, audios, videos, PDFs, and documents are allowed.') }}
                        </p>
                        @endif
                    </div>
                </div>
                
                <div id="file-preview" class="mt-4 hidden">
                    <h4 class="font-medium text-gray-700 dark:text-white mb-2">{{ __('Selected Files:') }}</h4>
                    <div id="file-list" class="space-y-2"></div>
                </div>
            </form>
            
            <div class="flex justify-end gap-3 mt-6">
                <button type="button" 
                        x-on:click="uploadModalOpen = false"
                        class="btn-default">
                    {{ __('Cancel') }}
                </button>
                <button type="button" 
                        id="upload-btn"
                        onclick="uploadFiles()"
                        class="btn-primary">
                    {{ __('Upload Files') }}
                </button>
            </div>
        </div>
    </div>
</div>

<script>
const uploadLimits = @json($uploadLimits);
const isDemoMode = {{ config('app.demo_mode', false) ? 'true' : 'false' }};
const allowedDemoMimeTypes = @json(config('app.demo_mode', false) ? \App\Support\Helper\MediaHelper::getAllowedMimeTypesForDemo() : []);

// Function to check if file type is allowed in demo mode
function isFileAllowedInDemo(fileType) {
    if (!isDemoMode) return true;
    return allowedDemoMimeTypes.includes(fileType);
}

document.getElementById('file-input').addEventListener('change', function(e) {
    const files = Array.from(e.target.files);
    const preview = document.getElementById('file-preview');
    const fileList = document.getElementById('file-list');
    
    // Validate files before showing them
    const validFiles = [];
    const errors = [];
    
    if (files.length > uploadLimits.max_file_uploads) {
        errors.push(`{{ __('You can upload a maximum of :max files at once.', ['max' => '']) }}${uploadLimits.max_file_uploads}`);
    }
    
    let totalSize = 0;
    files.forEach((file, index) => {
        totalSize += file.size;
        
        // Check demo mode restrictions
        if (isDemoMode && !isFileAllowedInDemo(file.type)) {
            errors.push(`{{ __('File ":name" is not allowed in demo mode. Only images, videos, PDFs, and documents are permitted.', ['name' => '']) }}${file.name}"`);
            return;
        }
        
        if (file.size > uploadLimits.effective_max_filesize) {
            errors.push(`{{ __('File ":name" exceeds the maximum size of :max', ['name' => '', 'max' => '']) }}${file.name}" exceeds ${uploadLimits.effective_max_filesize_formatted}`);
        } else {
            validFiles.push(file);
        }
    });
    
    if (totalSize > uploadLimits.post_max_size) {
        errors.push(`{{ __('Total upload size exceeds the limit of :max', ['max' => '']) }}${uploadLimits.post_max_size_formatted}`);
    }
    
    if (errors.length > 0) {
        alert(errors.join('\n'));
        this.value = '';
        preview.classList.add('hidden');
        return;
    }
    
    if (validFiles.length > 0) {
        preview.classList.remove('hidden');
        fileList.innerHTML = '';
        
        validFiles.forEach((file, index) => {
            const fileItem = document.createElement('div');
            fileItem.className = 'flex items-center justify-between p-2 bg-gray-50 dark:bg-gray-800 rounded';
            fileItem.innerHTML = `
                <div class="flex items-center">
                    <iconify-icon icon="lucide:file" class="text-gray-400 mr-2"></iconify-icon>
                    <span class="text-sm text-gray-700 dark:text-gray-300">${file.name}</span>
                    <span class="text-xs text-gray-500 ml-2">(${(file.size / 1024 / 1024).toFixed(2)} MB)</span>
                </div>
                <button type="button" onclick="removeFile(${index})" class="text-red-500 hover:text-red-700">
                    <iconify-icon icon="lucide:x" class="w-4 h-4"></iconify-icon>
                </button>
            `;
            fileList.appendChild(fileItem);
        });
    } else {
        preview.classList.add('hidden');
    }
});

function removeFile(index) {
    const fileInput = document.getElementById('file-input');
    const dt = new DataTransfer();
    const files = Array.from(fileInput.files);
    
    files.splice(index, 1);
    
    for (const file of files) {
        dt.items.add(file);
    }
    
    fileInput.files = dt.files;
    fileInput.dispatchEvent(new Event('change'));
}

function uploadFiles() {
    const fileInput = document.getElementById('file-input');
    const uploadBtn = document.getElementById('upload-btn');
    
    if (fileInput.files.length === 0) {
        alert('{{ __("Please select files to upload") }}');
        return;
    }
    
    const formData = new FormData();
    for (const file of fileInput.files) {
        formData.append('files[]', file);
    }
    formData.append('_token', '{{ csrf_token() }}');
    
    uploadBtn.disabled = true;
    uploadBtn.textContent = '{{ __("Uploading...") }}';
    
    fetch('{{ route("admin.media.store") }}', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(errorData => {
                throw new Error(errorData.message || `HTTP ${response.status}: ${response.statusText}`);
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            if (window.showToast) {
                window.showToast('success', '{{ __('Success') }}', data.message);
            }
            location.reload();
        } else {
            let errorMessage = data.message || '{{ __("Error uploading files") }}';
            
            // Handle validation errors
            if (data.errors) {
                const validationErrors = Object.values(data.errors).flat();
                errorMessage = validationErrors.join('\n');
            }
            
            if (data.error_type === 'php_upload_limit') {
                errorMessage += `\n\n{{ __('Upload size:') }} ${Math.round(data.uploaded_size / 1024 / 1024)} MB\n{{ __('PHP Limit:') }} ${data.limit_formatted}`;
            }
            
            alert(errorMessage);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert(error.message || '{{ __("Error uploading files") }}');
    })
    .finally(() => {
        uploadBtn.disabled = false;
        uploadBtn.textContent = '{{ __("Upload Files") }}';
    });
}

// Add drag and drop functionality
function dragOverHandler(ev) {
    ev.preventDefault();
    ev.dataTransfer.dropEffect = "copy";
    document.getElementById('drop-zone').classList.add('border-primary', 'bg-primary-50', 'dark:bg-primary-900/20');
}

function dragLeaveHandler(ev) {
    ev.preventDefault();
    document.getElementById('drop-zone').classList.remove('border-primary', 'bg-primary-50', 'dark:bg-primary-900/20');
}

function dropHandler(ev) {
    ev.preventDefault();
    document.getElementById('drop-zone').classList.remove('border-primary', 'bg-primary-50', 'dark:bg-primary-900/20');
    
    const files = ev.dataTransfer.files;
    document.getElementById('file-input').files = files;
    document.getElementById('file-input').dispatchEvent(new Event('change'));
}
</script>
