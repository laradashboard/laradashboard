<x-layouts.backend-layout :breadcrumbs="$breadcrumbs">
    <div id="module-uploader"
         x-data="{
            files: [],
            isDragging: false,
            maxUploadBytes: {{ $maxUploadBytes }},
            maxUploadFormatted: '{{ $maxUploadFormatted }}',

            handleDrop(event) {
                this.isDragging = false;
                const droppedFiles = Array.from(event.dataTransfer.files);
                this.addFiles(droppedFiles);
            },

            handleFileSelect(event) {
                const selectedFiles = Array.from(event.target.files);
                this.addFiles(selectedFiles);
                event.target.value = '';
            },

            addFiles(newFiles) {
                newFiles.forEach(file => {
                    if (!file.name.toLowerCase().endsWith('.zip')) {
                        this.showToast('error', '{{ __('Invalid File') }}', file.name + ' - {{ __('Only .zip files are allowed.') }}');
                        return;
                    }
                    if (file.size > this.maxUploadBytes) {
                        this.showToast('error', '{{ __('File Too Large') }}', file.name + ' - {{ __('Maximum size is') }} ' + this.maxUploadFormatted);
                        return;
                    }
                    // Check if file already exists
                    if (this.files.some(f => f.file.name === file.name)) {
                        this.showToast('warning', '{{ __('Duplicate') }}', file.name + ' {{ __('is already in the queue.') }}');
                        return;
                    }
                    this.files.push({
                        file: file,
                        status: 'pending',
                        progress: 0,
                        message: '',
                        moduleName: '',
                        currentStep: 0,
                        steps: [
                            { label: '{{ __('Uploading') }}', status: 'pending' },
                            { label: '{{ __('Extracting') }}', status: 'pending' },
                            { label: '{{ __('Validating') }}', status: 'pending' },
                            { label: '{{ __('Installing') }}', status: 'pending' },
                        ]
                    });
                });
            },

            removeFile(index) {
                if (this.files[index].status === 'uploading') return;
                this.files.splice(index, 1);
            },

            formatFileSize(bytes) {
                if (bytes >= 1024 * 1024) return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
                if (bytes >= 1024) return (bytes / 1024).toFixed(2) + ' KB';
                return bytes + ' bytes';
            },

            showToast(type, title, message) {
                if (window.showToast) {
                    window.showToast(type, title, message);
                }
            },

            get pendingFiles() {
                return this.files.filter(f => f.status === 'pending');
            },

            get hasFilesToUpload() {
                return this.pendingFiles.length > 0;
            },

            get isUploading() {
                return this.files.some(f => f.status === 'uploading');
            },

            get completedCount() {
                return this.files.filter(f => f.status === 'success').length;
            },

            get failedCount() {
                return this.files.filter(f => f.status === 'error').length;
            },

            async startUpload() {
                const pendingFiles = this.files.filter(f => f.status === 'pending');
                for (const fileItem of pendingFiles) {
                    await this.uploadFile(fileItem);
                }
            },

            async uploadFile(fileItem) {
                fileItem.status = 'uploading';
                fileItem.currentStep = 0;
                fileItem.steps.forEach(s => s.status = 'pending');

                // Step 1: Upload
                fileItem.steps[0].status = 'processing';

                const formData = new FormData();
                formData.append('module', fileItem.file);
                formData.append('_token', '{{ csrf_token() }}');

                try {
                    const response = await this.uploadWithProgress(fileItem, formData);

                    if (!response.ok) {
                        const errorData = await response.json();
                        throw new Error(errorData.message || '{{ __('Upload failed') }}');
                    }

                    fileItem.steps[0].status = 'complete';
                    fileItem.progress = 100;

                    // Step 2: Extracting
                    fileItem.currentStep = 1;
                    fileItem.steps[1].status = 'processing';
                    await this.delay(400);
                    fileItem.steps[1].status = 'complete';

                    // Step 3: Validating
                    fileItem.currentStep = 2;
                    fileItem.steps[2].status = 'processing';
                    await this.delay(400);
                    fileItem.steps[2].status = 'complete';

                    // Step 4: Installing
                    fileItem.currentStep = 3;
                    fileItem.steps[3].status = 'processing';
                    await this.delay(400);
                    fileItem.steps[3].status = 'complete';

                    const data = await response.json();
                    fileItem.moduleName = data.module_name || '';
                    fileItem.message = data.message || '{{ __('Module installed successfully') }}';
                    fileItem.status = 'success';

                } catch (error) {
                    fileItem.steps[fileItem.currentStep].status = 'error';
                    fileItem.message = error.message;
                    fileItem.status = 'error';
                }
            },

            uploadWithProgress(fileItem, formData) {
                return new Promise((resolve, reject) => {
                    const xhr = new XMLHttpRequest();

                    xhr.upload.addEventListener('progress', (event) => {
                        if (event.lengthComputable) {
                            fileItem.progress = Math.round((event.loaded / event.total) * 100);
                        }
                    });

                    xhr.addEventListener('load', () => {
                        resolve({
                            ok: xhr.status >= 200 && xhr.status < 300,
                            status: xhr.status,
                            json: () => Promise.resolve(JSON.parse(xhr.responseText))
                        });
                    });

                    xhr.addEventListener('error', () => reject(new Error('{{ __('Network error') }}')));

                    xhr.open('POST', '{{ route('admin.modules.upload-ajax') }}');
                    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                    xhr.send(formData);
                });
            },

            delay(ms) {
                return new Promise(resolve => setTimeout(resolve, ms));
            },

            async activateModule(fileItem) {
                if (!fileItem.moduleName) return;

                try {
                    const response = await fetch('/admin/modules/toggle-status/' + fileItem.moduleName, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' },
                    });
                    const data = await response.json();
                    if (data.success) {
                        fileItem.activated = true;
                        this.showToast('success', '{{ __('Success') }}', '{{ __('Module activated successfully!') }}');
                    }
                } catch (error) {
                    this.showToast('error', '{{ __('Error') }}', '{{ __('Failed to activate module') }}');
                }
            },

            activateAll() {
                this.files.filter(f => f.status === 'success' && !f.activated && f.moduleName).forEach(f => {
                    this.activateModule(f);
                });
            }
         }"
         x-cloak>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left: Drop Zone -->
            <div class="lg:col-span-1">
                <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-6 sticky top-6">
                    <h3 class="font-medium text-gray-900 dark:text-white mb-4">{{ __('Add Modules') }}</h3>

                    <!-- Drop Zone -->
                    <div class="border-2 border-dashed rounded-lg p-6 text-center transition-all duration-200 cursor-pointer"
                         :class="isDragging ? 'border-primary bg-primary/5 dark:bg-primary/10' : 'border-gray-300 dark:border-gray-600 hover:border-primary hover:bg-gray-50 dark:hover:bg-gray-700/50'"
                         @click="$refs.fileInput.click()"
                         @dragover.prevent="isDragging = true"
                         @dragleave.prevent="isDragging = false"
                         @drop.prevent="handleDrop($event)">

                        <div class="flex flex-col items-center">
                            <div class="w-12 h-12 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center mb-3">
                                <iconify-icon icon="lucide:upload-cloud" class="text-2xl text-gray-400"></iconify-icon>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-300 font-medium mb-1">
                                {{ __('Drop files here') }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ __('or click to browse') }}
                            </p>
                        </div>

                        <input type="file" x-ref="fileInput" accept=".zip" multiple class="hidden" @change="handleFileSelect($event)">
                    </div>

                    <div class="mt-4 text-xs text-gray-500 dark:text-gray-400 space-y-1">
                        <p class="flex items-center gap-2">
                            <iconify-icon icon="lucide:info" class="text-sm"></iconify-icon>
                            {{ __('Only .zip files are allowed') }}
                        </p>
                        <p class="flex items-center gap-2">
                            <iconify-icon icon="lucide:hard-drive" class="text-sm"></iconify-icon>
                            {{ __('Max size:') }} {{ $maxUploadFormatted }}
                        </p>
                    </div>

                    @if(config('app.demo_mode', false))
                    <div class="mt-4 p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg">
                        <p class="text-xs text-amber-700 dark:text-amber-400 flex items-center gap-2">
                            <iconify-icon icon="lucide:alert-triangle"></iconify-icon>
                            {{ __('Uploads disabled in demo mode') }}
                        </p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Right: Upload Queue -->
            <div class="lg:col-span-2">
                <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
                    <!-- Queue Header -->
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <h3 class="font-medium text-gray-900 dark:text-white">{{ __('Upload Queue') }}</h3>
                            <div class="flex items-center gap-2 text-sm">
                                <span x-show="completedCount > 0" class="px-2 py-0.5 bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 rounded-full text-xs">
                                    <span x-text="completedCount"></span> {{ __('completed') }}
                                </span>
                                <span x-show="failedCount > 0" class="px-2 py-0.5 bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400 rounded-full text-xs">
                                    <span x-text="failedCount"></span> {{ __('failed') }}
                                </span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <button x-show="completedCount > 0 && files.some(f => f.status === 'success' && !f.activated)"
                                    @click="activateAll()"
                                    class="btn-secondary text-sm">
                                <iconify-icon icon="lucide:power" class="mr-1"></iconify-icon>
                                {{ __('Activate All') }}
                            </button>
                            <button x-show="hasFilesToUpload && !isUploading"
                                    @click="startUpload()"
                                    class="btn-primary text-sm">
                                <iconify-icon icon="lucide:upload" class="mr-1"></iconify-icon>
                                {{ __('Install') }} (<span x-text="pendingFiles.length"></span>)
                            </button>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div x-show="files.length === 0" class="p-12 text-center">
                        <div class="w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center mx-auto mb-4">
                            <iconify-icon icon="lucide:package" class="text-3xl text-gray-400"></iconify-icon>
                        </div>
                        <p class="text-gray-500 dark:text-gray-400">{{ __('No modules in queue') }}</p>
                        <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">{{ __('Drop files or click to add modules') }}</p>
                    </div>

                    <!-- File List -->
                    <div x-show="files.length > 0" class="divide-y divide-gray-200 dark:divide-gray-700">
                        <template x-for="(fileItem, index) in files" :key="index">
                            <div class="p-4">
                                <div class="flex items-start gap-4">
                                    <!-- Status Icon -->
                                    <div class="flex-shrink-0 w-10 h-10 rounded-lg flex items-center justify-center"
                                         :class="{
                                             'bg-gray-100 dark:bg-gray-700': fileItem.status === 'pending',
                                             'bg-blue-100 dark:bg-blue-900/30': fileItem.status === 'uploading',
                                             'bg-green-100 dark:bg-green-900/30': fileItem.status === 'success',
                                             'bg-red-100 dark:bg-red-900/30': fileItem.status === 'error'
                                         }">
                                        <iconify-icon x-show="fileItem.status === 'pending'" icon="lucide:file-archive" class="text-xl text-gray-500"></iconify-icon>
                                        <iconify-icon x-show="fileItem.status === 'uploading'" icon="lucide:loader-2" class="text-xl text-blue-500 animate-spin"></iconify-icon>
                                        <iconify-icon x-show="fileItem.status === 'success'" icon="lucide:check-circle" class="text-xl text-green-500"></iconify-icon>
                                        <iconify-icon x-show="fileItem.status === 'error'" icon="lucide:x-circle" class="text-xl text-red-500"></iconify-icon>
                                    </div>

                                    <!-- File Info -->
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between mb-1">
                                            <p class="font-medium text-gray-900 dark:text-white truncate" x-text="fileItem.file.name"></p>
                                            <div class="flex items-center gap-2">
                                                <span class="text-xs text-gray-500" x-text="formatFileSize(fileItem.file.size)"></span>
                                                <button x-show="fileItem.status === 'pending' || fileItem.status === 'error'"
                                                        @click="removeFile(index)"
                                                        class="p-1 text-gray-400 hover:text-red-500 rounded">
                                                    <iconify-icon icon="lucide:x" class="text-sm"></iconify-icon>
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Progress Steps (while uploading) -->
                                        <div x-show="fileItem.status === 'uploading'" class="mt-3">
                                            <div class="flex items-center gap-1 mb-2">
                                                <template x-for="(step, stepIndex) in fileItem.steps" :key="stepIndex">
                                                    <div class="flex items-center">
                                                        <div class="w-6 h-6 rounded-full flex items-center justify-center text-xs"
                                                             :class="{
                                                                 'bg-green-500 text-white': step.status === 'complete',
                                                                 'bg-primary text-white animate-pulse': step.status === 'processing',
                                                                 'bg-gray-200 dark:bg-gray-600 text-gray-500': step.status === 'pending',
                                                                 'bg-red-500 text-white': step.status === 'error'
                                                             }">
                                                            <iconify-icon x-show="step.status === 'complete'" icon="lucide:check" class="text-xs"></iconify-icon>
                                                            <iconify-icon x-show="step.status === 'processing'" icon="lucide:loader-2" class="text-xs animate-spin"></iconify-icon>
                                                            <iconify-icon x-show="step.status === 'error'" icon="lucide:x" class="text-xs"></iconify-icon>
                                                            <span x-show="step.status === 'pending'" x-text="stepIndex + 1"></span>
                                                        </div>
                                                        <div x-show="stepIndex < fileItem.steps.length - 1" class="w-4 h-0.5 mx-0.5"
                                                             :class="step.status === 'complete' ? 'bg-green-500' : 'bg-gray-200 dark:bg-gray-600'"></div>
                                                    </div>
                                                </template>
                                            </div>
                                            <div class="flex items-center gap-2 text-xs text-gray-500">
                                                <span x-text="fileItem.steps[fileItem.currentStep]?.label || ''"></span>
                                                <span x-show="fileItem.currentStep === 0">(<span x-text="fileItem.progress"></span>%)</span>
                                            </div>
                                        </div>

                                        <!-- Progress Bar (while uploading step 1) -->
                                        <div x-show="fileItem.status === 'uploading' && fileItem.currentStep === 0" class="mt-2">
                                            <div class="h-1.5 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                                                <div class="h-full bg-primary rounded-full transition-all duration-300"
                                                     :style="'width: ' + fileItem.progress + '%'"></div>
                                            </div>
                                        </div>

                                        <!-- Success Message -->
                                        <div x-show="fileItem.status === 'success'" class="mt-2 flex items-center justify-between">
                                            <p class="text-sm text-green-600 dark:text-green-400" x-text="fileItem.message"></p>
                                            <button x-show="fileItem.moduleName && !fileItem.activated"
                                                    @click="activateModule(fileItem)"
                                                    class="text-xs px-3 py-1 bg-primary text-white rounded-md hover:bg-primary/90">
                                                {{ __('Activate') }}
                                            </button>
                                            <span x-show="fileItem.activated" class="text-xs px-3 py-1 bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 rounded-md">
                                                {{ __('Activated') }}
                                            </span>
                                        </div>

                                        <!-- Error Message -->
                                        <div x-show="fileItem.status === 'error'" class="mt-2">
                                            <p class="text-sm text-red-600 dark:text-red-400" x-text="fileItem.message"></p>
                                        </div>

                                        <!-- Pending Status -->
                                        <div x-show="fileItem.status === 'pending'" class="mt-1">
                                            <p class="text-xs text-gray-400">{{ __('Waiting to upload...') }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Summary Footer -->
                    <div x-show="files.length > 0 && !isUploading && (completedCount > 0 || failedCount > 0)"
                         class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                        <div class="flex items-center justify-between">
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                <span x-show="completedCount > 0">
                                    <span x-text="completedCount"></span> {{ __('module(s) installed successfully.') }}
                                </span>
                                <span x-show="failedCount > 0" class="text-red-600 dark:text-red-400">
                                    <span x-text="failedCount"></span> {{ __('failed.') }}
                                </span>
                            </p>
                            <div class="flex items-center gap-2">
                                <button x-show="files.some(f => f.status === 'success' && !f.activated && f.moduleName)"
                                        @click="activateAll()"
                                        class="btn-secondary text-sm">
                                    <iconify-icon icon="lucide:power" class="mr-1"></iconify-icon>
                                    {{ __('Activate All') }}
                                </button>
                                <a href="{{ route('admin.modules.index') }}" class="btn-primary text-sm">
                                    {{ __('View All Modules') }}
                                    <iconify-icon icon="lucide:arrow-right" class="ml-1"></iconify-icon>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.backend-layout>
