@extends('backend.layouts.app')

@section('title')
    {{ $breadcrumbs['title'] }} | {{ config('app.name') }}
@endsection

@section('admin-content')
    <div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6" x-data="{
        selectedMedia: [],
        selectAll: false,
        bulkDeleteModalOpen: false,
        typeDropdownOpen: false,
        bulkActionsDropdownOpen: false,
        viewMode: localStorage.getItem('mediaViewMode') || 'grid',
        uploadModalOpen: false,

        toggleViewMode() {
            this.viewMode = this.viewMode === 'grid' ? 'list' : 'grid';
            localStorage.setItem('mediaViewMode', this.viewMode);
        },

        showSingleDeleteModal(id) {
            this.selectedMedia = [id.toString()];
            this.bulkDeleteModalOpen = true;
        }
    }" id="mediaManager">
        <x-breadcrumbs :breadcrumbs="$breadcrumbs" />

        {!! ld_apply_filters('media_after_breadcrumbs', '') !!}

        @if ($errors->any())
            <div class="mb-6 p-4 border border-red-200 bg-red-50 rounded-md dark:border-red-800 dark:bg-red-900/20">
                <div class="flex">
                    <iconify-icon icon="lucide:alert-circle" class="text-red-500 text-xl mr-3 mt-0.5"></iconify-icon>
                    <div>
                        <h3 class="text-sm font-medium text-red-800 dark:text-red-200 mb-2">
                            {{ __('Upload Error') }}
                        </h3>
                        <ul class="text-sm text-red-700 dark:text-red-300 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        @if ($errors->has('upload_error'))
                            <div class="mt-3 p-3 bg-red-100 dark:bg-red-900/40 rounded border border-red-200 dark:border-red-700">
                                <p class="text-xs text-red-600 dark:text-red-400 font-medium mb-2">{{ __('Current PHP Upload Limits:') }}</p>
                                <ul class="text-xs text-red-600 dark:text-red-400 space-y-1">
                                    <li>{{ __('Max file size:') }} {{ $uploadLimits['effective_max_filesize_formatted'] }}</li>
                                    <li>{{ __('Max total upload:') }} {{ $uploadLimits['post_max_size_formatted'] }}</li>
                                    <li>{{ __('Max files:') }} {{ $uploadLimits['max_file_uploads'] }}</li>
                                </ul>
                                <p class="text-xs text-red-600 dark:text-red-400 mt-2">
                                    {{ __('Contact your administrator to increase PHP upload limits.') }}
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-2 gap-4 mb-6 md:grid-cols-6 md:gap-6">
            <div class="p-4 bg-white rounded-md border border-gray-200 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center">
                    <iconify-icon icon="lucide:files" class="text-2xl text-blue-500 mr-3"></iconify-icon>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-300">{{ __('Total Files') }}</p>
                        <p class="text-lg font-semibold text-gray-700 dark:text-white">{{ $stats['total'] }}</p>
                    </div>
                </div>
            </div>

            <div class="p-4 bg-white rounded-md border border-gray-200 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center">
                    <iconify-icon icon="lucide:image" class="text-2xl text-green-500 mr-3"></iconify-icon>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-300">{{ __('Images') }}</p>
                        <p class="text-lg font-semibold text-gray-700 dark:text-white">{{ $stats['images'] }}</p>
                    </div>
                </div>
            </div>

            <div class="p-4 bg-white rounded-md border border-gray-200 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center">
                    <iconify-icon icon="lucide:video" class="text-2xl text-purple-500 mr-3"></iconify-icon>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-300">{{ __('Videos') }}</p>
                        <p class="text-lg font-semibold text-gray-700 dark:text-white">{{ $stats['videos'] }}</p>
                    </div>
                </div>
            </div>

            <div class="p-4 bg-white rounded-md border border-gray-200 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center">
                    <iconify-icon icon="lucide:music" class="text-2xl text-emerald-500 mr-3"></iconify-icon>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-300">{{ __('Audio') }}</p>
                        <p class="text-lg font-semibold text-gray-700 dark:text-white">{{ $stats['audio'] ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="p-4 bg-white rounded-md border border-gray-200 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center">
                    <iconify-icon icon="lucide:file-text" class="text-2xl text-orange-500 mr-3"></iconify-icon>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-300">{{ __('Documents') }}</p>
                        <p class="text-lg font-semibold text-gray-700 dark:text-white">{{ $stats['documents'] }}</p>
                    </div>
                </div>
            </div>

            <div class="p-4 bg-white rounded-md border border-gray-200 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center">
                    <iconify-icon icon="lucide:hard-drive" class="text-2xl text-red-500 mr-3"></iconify-icon>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-300">{{ __('Storage Used') }}</p>
                        <p class="text-lg font-semibold text-gray-700 dark:text-white">{{ $stats['total_size'] }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded-md border border-gray-200  dark:border-gray-800 bg-white dark:bg-white/[0.03]">
                <div class="px-5 py-4 sm:px-6 sm:py-5 flex flex-col md:flex-row justify-between items-center gap-3">
                    @include('backend.partials.search-form', [
                        'placeholder' => __('Search media files...'),
                    ])

                    <div class="flex items-center gap-3">
                        <!-- Bulk Actions dropdown -->
                        <div class="flex items-center justify-center relative" x-show="selectedMedia.length > 0">
                            <button @click="bulkActionsDropdownOpen = !bulkActionsDropdownOpen"
                                class="btn-secondary flex items-center justify-center gap-2 text-sm" type="button">
                                <iconify-icon icon="lucide:more-vertical"></iconify-icon>
                                <span>{{ __('Bulk Actions') }} (<span x-text="selectedMedia.length"></span>)</span>
                                <iconify-icon icon="lucide:chevron-down"></iconify-icon>
                            </button>

                            <!-- Bulk Actions dropdown menu -->
                            <div x-show="bulkActionsDropdownOpen" @click.away="bulkActionsDropdownOpen = false"
                                x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="transform opacity-0 scale-95"
                                x-transition:enter-end="transform opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-75"
                                x-transition:leave-start="transform opacity-100 scale-100"
                                x-transition:leave-end="transform opacity-0 scale-95"
                                class="absolute top-full right-0 z-10 w-48 p-2 bg-white rounded-md shadow-lg dark:bg-gray-700 mt-2">
                                <ul class="space-y-2">
                                    <li class="cursor-pointer flex items-center gap-2 text-sm text-red-600 dark:text-red-500 hover:bg-red-50 dark:hover:bg-red-500 dark:hover:text-red-50 px-2 py-1.5 rounded transition-colors duration-300"
                                        @click="bulkDeleteModalOpen = true; bulkActionsDropdownOpen = false">
                                        <iconify-icon icon="lucide:trash"></iconify-icon>
                                        {{ __('Delete Selected') }}
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <!-- Type Filter dropdown -->
                        <div class="flex items-center justify-center relative">
                            <button @click="typeDropdownOpen = !typeDropdownOpen"
                                class="btn-secondary flex items-center justify-center gap-2 text-sm" type="button">
                                <iconify-icon icon="lucide:filter"></iconify-icon>
                                <span class="hidden sm:inline">{{ __('Type') }}</span>
                                @if (request('type'))
                                    <span
                                        class="px-2 py-0.5 text-xs bg-blue-100 text-blue-800 rounded-full dark:bg-blue-900/20 dark:text-primary">
                                        {{ ucfirst(request('type')) }}
                                    </span>
                                @endif
                                <iconify-icon icon="lucide:chevron-down"></iconify-icon>
                            </button>

                            <!-- Type dropdown menu -->
                            <div x-show="typeDropdownOpen" @click.away="typeDropdownOpen = false"
                                x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="transform opacity-0 scale-95"
                                x-transition:enter-end="transform opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-75"
                                x-transition:leave-start="transform opacity-100 scale-100"
                                x-transition:leave-end="transform opacity-0 scale-95"
                                class="absolute top-full right-0 z-10 w-48 p-2 bg-white rounded-md shadow-lg dark:bg-gray-700 mt-2">
                                <ul class="space-y-2">
                                    <li>
                                        <a href="{{ route('admin.media.index', array_merge(request()->query(), ['type' => null])) }}"
                                            @click="typeDropdownOpen = false"
                                            class="cursor-pointer flex items-center gap-2 text-sm text-gray-700 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-400 hover:opacity-80 px-2 py-1.5 rounded transition-colors duration-300 {{ !request('type') ? 'bg-gray-100 dark:bg-gray-600' : '' }}">
                                            <iconify-icon icon="lucide:layers"
                                                class="text-gray-500 dark:text-gray-400"></iconify-icon>
                                            {{ __('All Types') }}
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('admin.media.index', array_merge(request()->query(), ['type' => 'images'])) }}"
                                            @click="typeDropdownOpen = false"
                                            class="cursor-pointer flex items-center gap-2 text-sm text-gray-700 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-600 px-2 py-1.5 rounded transition-colors duration-300 {{ request('type') === 'images' ? 'bg-gray-100 dark:bg-gray-600' : '' }}">
                                            <iconify-icon icon="lucide:image" class="text-green-500"></iconify-icon>
                                            {{ __('Images') }}
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('admin.media.index', array_merge(request()->query(), ['type' => 'videos'])) }}"
                                            @click="typeDropdownOpen = false"
                                            class="cursor-pointer flex items-center gap-2 text-sm text-gray-700 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-600 px-2 py-1.5 rounded transition-colors duration-300 {{ request('type') === 'videos' ? 'bg-gray-100 dark:bg-gray-600' : '' }}">
                                            <iconify-icon icon="lucide:video" class="text-purple-500"></iconify-icon>
                                            {{ __('Videos') }}
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('admin.media.index', array_merge(request()->query(), ['type' => 'audio'])) }}"
                                            @click="typeDropdownOpen = false"
                                            class="cursor-pointer flex items-center gap-2 text-sm text-gray-700 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-600 px-2 py-1.5 rounded transition-colors duration-300 {{ request('type') === 'audio' ? 'bg-gray-100 dark:bg-gray-600' : '' }}">
                                            <iconify-icon icon="lucide:music" class="text-emerald-500"></iconify-icon>
                                            {{ __('Audio') }}
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('admin.media.index', array_merge(request()->query(), ['type' => 'documents'])) }}"
                                            @click="typeDropdownOpen = false"
                                            class="cursor-pointer flex items-center gap-2 text-sm text-gray-700 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-600 px-2 py-1.5 rounded transition-colors duration-300 {{ request('type') === 'documents' ? 'bg-gray-100 dark:bg-gray-600' : '' }}">
                                            <iconify-icon icon="lucide:file-text" class="text-orange-500"></iconify-icon>
                                            {{ __('Documents') }}
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <!-- View Mode Toggle -->
                        <button @click="toggleViewMode()" class="btn-secondary flex items-center gap-2">
                            <iconify-icon :icon="viewMode === 'grid' ? 'lucide:list' : 'lucide:grid-3x3'"
                                class="text-sm"></iconify-icon>
                            <span class="hidden sm:inline"
                                x-text="viewMode === 'grid' ? '{{ __('List View') }}' : '{{ __('Grid View') }}'"></span>
                        </button>

                        @if (auth()->user()->can('media.create') && count($media))
                            <button @click="uploadModalOpen = true" class="btn-primary flex items-center gap-2">
                                <iconify-icon icon="lucide:upload" height="16"></iconify-icon>
                                {{ __('Upload Media') }}
                            </button>
                        @endif
                    </div>
                </div>

                <div class="border-t border-gray-100 dark:border-gray-800">
                    <!-- Grid View -->
                    <div x-show="viewMode === 'grid'" class="p-5 sm:p-6">
                        @if ($media->count() > 0)
                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                                @foreach ($media as $item)
                                    <div
                                        class="relative group border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden hover:shadow-md transition-shadow duration-200 bg-white dark:bg-gray-800">
                                        <div class="absolute top-2 left-2 z-10 transition-opacity duration-200"
                                             :class="selectedMedia.includes('{{ $item->id }}') ? 'opacity-100' : 'opacity-0 group-hover:opacity-100'">
                                            <input type="checkbox" value="{{ $item->id }}" x-model="selectedMedia"
                                                class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded focus:ring-primary dark:focus:ring-primary dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                        </div>

                                        <div class="aspect-square">
                                            @if (str_starts_with($item->mime_type, 'image/'))
                                                <img src="{{ $item->url ?? asset('storage/media/' . $item->file_name) }}"
                                                    alt="{{ $item->name }}" class="w-full h-full object-cover"
                                                    loading="lazy">
                                            @elseif(str_starts_with($item->mime_type, 'video/'))
                                                <div class="w-full h-full bg-black rounded-lg overflow-hidden relative">
                                                    <video
                                                        class="w-full h-full object-cover"
                                                        preload="metadata"
                                                        style="background: linear-gradient(135deg, rgb(147 51 234 / 0.1) 0%, rgb(147 51 234 / 0.2) 100%)"
                                                        muted
                                                    >
                                                        <source src="{{ $item->url ?? asset('storage/media/' . $item->file_name) }}" type="{{ $item->mime_type }}">
                                                    </video>
                                                    <div class="absolute inset-0 bg-black/40 flex items-center justify-center">
                                                        <div class="text-center">
                                                            <iconify-icon icon="lucide:play" class="text-4xl text-white mb-2"></iconify-icon>
                                                            <p class="text-xs text-white font-medium">Video</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            @elseif(str_starts_with($item->mime_type, 'audio/'))
                                                <div class="w-full h-full bg-gradient-to-br from-green-100 to-emerald-200 dark:from-green-900 dark:to-emerald-800 flex flex-col">
                                                    <div class="flex-1 flex items-center justify-center">
                                                        <div class="text-center">
                                                            <iconify-icon icon="lucide:music" class="text-4xl text-green-600 dark:text-green-300 mb-2"></iconify-icon>
                                                            <p class="text-xs text-green-600 dark:text-green-300 font-medium">Audio</p>
                                                        </div>
                                                    </div>
                                                    <div class="p-2 bg-white/50 dark:bg-gray-800/50">
                                                        <audio class="w-full" style="height: 24px;" preload="metadata" onloadstart="this.volume=0.3">
                                                            <source src="{{ $item->url ?? asset('storage/media/' . $item->file_name) }}" type="{{ $item->mime_type }}">
                                                        </audio>
                                                    </div>
                                                </div>
                                            @elseif(str_starts_with($item->mime_type, 'application/pdf'))
                                                <div
                                                    class="w-full h-full bg-gradient-to-br from-red-100 to-red-200 dark:from-red-900 dark:to-red-800 flex items-center justify-center">
                                                    <div class="text-center">
                                                        <iconify-icon icon="lucide:file-text"
                                                            class="text-4xl text-red-600 dark:text-red-300 mb-2"></iconify-icon>
                                                        <p class="text-xs text-red-600 dark:text-red-300 font-medium">PDF
                                                        </p>
                                                    </div>
                                                </div>
                                            @else
                                                <div
                                                    class="w-full h-full bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-600 flex items-center justify-center">
                                                    <div class="text-center">
                                                        <iconify-icon icon="lucide:file"
                                                            class="text-4xl text-gray-600 dark:text-gray-300 mb-2"></iconify-icon>
                                                        <p class="text-xs text-gray-600 dark:text-gray-300 font-medium">
                                                            {{ strtoupper(pathinfo($item->file_name, PATHINFO_EXTENSION)) }}
                                                        </p>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>

                                        <div
                                            class="p-3 border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
                                            <p class="text-xs font-medium text-gray-700 dark:text-white truncate"
                                                title="{{ $item->name }}">
                                                {{ $item->name }}
                                            </p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                {{ strtoupper(pathinfo($item->file_name, PATHINFO_EXTENSION)) }} •
                                                {{ \Illuminate\Support\Str::limit($item->human_readable_size, 10) }}
                                            </p>
                                        </div>

                                        <!-- Actions overlay -->
                                        <div
                                            class="absolute inset-0 bg-white/10 backdrop-blur-sm opacity-0 group-hover:opacity-100 transition-all duration-300 flex items-center justify-center gap-2">
                                            @if (str_starts_with($item->mime_type, 'image/'))
                                                <button
                                                    class="p-2 bg-white/90 backdrop-blur-sm rounded-md text-gray-700 hover:bg-white transition-colors shadow-lg"
                                                    onclick="openImageModal('{{ $item->url ?? asset('storage/media/' . $item->file_name) }}', '{{ $item->name }}')"
                                                    title="{{ __('View') }}">
                                                    <iconify-icon icon="lucide:eye" class="text-sm"></iconify-icon>
                                                </button>
                                            @elseif (str_starts_with($item->mime_type, 'video/'))
                                                <button
                                                    class="p-2 bg-white/90 backdrop-blur-sm rounded-md text-gray-700 hover:bg-white transition-colors shadow-lg"
                                                    onclick="openVideoModal('{{ $item->url ?? asset('storage/media/' . $item->file_name) }}', '{{ $item->name }}')"
                                                    title="{{ __('Play Video') }}">
                                                    <iconify-icon icon="lucide:play" class="text-sm"></iconify-icon>
                                                </button>
                                            @elseif (str_starts_with($item->mime_type, 'audio/'))
                                                <button
                                                    class="p-2 bg-white/90 backdrop-blur-sm rounded-md text-gray-700 hover:bg-white transition-colors shadow-lg"
                                                    onclick="openAudioModal('{{ $item->url ?? asset('storage/media/' . $item->file_name) }}', '{{ $item->name }}', { name: '{{ $item->name }}', human_readable_size: '{{ $item->human_readable_size }}', extension: '{{ pathinfo($item->file_name, PATHINFO_EXTENSION) }}', duration: {{ $item->duration ?? 'null' }} })"
                                                    title="{{ __('Play Audio') }}">
                                                    <iconify-icon icon="lucide:headphones" class="text-sm"></iconify-icon>
                                                </button>
                                            @elseif (str_starts_with($item->mime_type, 'application/pdf'))
                                                <button
                                                    class="p-2 bg-white/90 backdrop-blur-sm rounded-md text-gray-700 hover:bg-white transition-colors shadow-lg"
                                                    onclick="openPdfModal('{{ $item->url ?? asset('storage/media/' . $item->file_name) }}', '{{ $item->name }}')"
                                                    title="{{ __('View PDF') }}">
                                                    <iconify-icon icon="lucide:eye" class="text-sm"></iconify-icon>
                                                </button>
                                            @endif
                                            <a href="{{ $item->url ?? asset('storage/media/' . $item->file_name) }}"
                                                download="{{ $item->name }}"
                                                class="p-2 bg-white/90 backdrop-blur-sm rounded-md text-gray-700 hover:bg-white transition-colors shadow-lg"
                                                title="{{ __('Download') }}">
                                                <iconify-icon icon="lucide:download" class="text-sm"></iconify-icon>
                                            </a>
                                            <button
                                                class="p-2 bg-white/90 backdrop-blur-sm rounded-md text-gray-700 hover:bg-white transition-colors shadow-lg"
                                                onclick="copyToClipboard('{{ $item->url ?? asset('storage/media/' . $item->file_name) }}')"
                                                title="{{ __('Copy URL') }}">
                                                <iconify-icon icon="lucide:copy" class="text-sm"></iconify-icon>
                                            </a>
                                            @if (auth()->user()->can('media.delete'))
                                                <button
                                                    class="p-2 bg-red-500/90 backdrop-blur-sm rounded-md text-white hover:bg-red-500 transition-colors shadow-lg"
                                                    @click="showSingleDeleteModal({{ $item->id }})"
                                                    title="{{ __('Delete') }}">
                                                    <iconify-icon icon="lucide:trash" class="text-sm"></iconify-icon>
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-12">
                                <iconify-icon icon="lucide:image"
                                    class="text-6xl text-gray-300 dark:text-gray-600 mb-4 mx-auto"></iconify-icon>
                                <p class="text-gray-500 dark:text-gray-400 mb-4">{{ __('No media files found') }}</p>

                                @if (auth()->user()->can('media.create'))
                                    <div class="flex justify-center">
                                        <button @click="uploadModalOpen = true" class="btn-primary flex items-center gap-2">
                                            <iconify-icon icon="lucide:upload" height="16"></iconify-icon>
                                            {{ __('Upload Media') }}
                                        </button>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>

                    <!-- List View -->
                    <div x-show="viewMode === 'list'" class="overflow-x-auto">
                        @if ($media->count() > 0)
                            <table class="table">
                                <thead class="table-thead">
                                    <tr class="table-tr">
                                        <th width="3%" class="table-thead-th">
                                            <input type="checkbox" x-model="selectAll"
                                                @click="selectAll = !selectAll; selectedMedia = selectAll ? [...document.querySelectorAll('.media-checkbox')].map(cb => cb.value) : [];"
                                                class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded focus:ring-primary dark:focus:ring-primary dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                        </th>
                                        <th class="table-thead-th">
                                            {{ __('File') }}
                                        </th>
                                        <th class="table-thead-th">
                                            {{ __('Type') }}
                                        </th>
                                        <th class="table-thead-th">
                                            {{ __('Size') }}
                                        </th>
                                        <th class="table-thead-th">
                                            {{ __('Date') }}
                                        </th>
                                        <th class="table-thead-th table-thead-th-last">
                                            {{ __('Actions') }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="table-tbody">
                                    @foreach ($media as $item)
                                        <tr class="table-tr">
                                            <td class="table-td table-td-checkbox">
                                                <input type="checkbox" value="{{ $item->id }}"
                                                    x-model="selectedMedia"
                                                    class="media-checkbox form-checkbox h-4 w-4 text-primary border-gray-300 rounded focus:ring-primary dark:focus:ring-primary dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600 transition-opacity duration-200"
                                                    :class="selectedMedia.includes('{{ $item->id }}') ? 'opacity-100' : 'opacity-0 group-hover:opacity-100'">
                                            </td>
                                            <td class="table-td">
                                                <div class="flex-shrink-0 h-12 w-12 mr-3">
                                                    @if (str_starts_with($item->mime_type, 'image/'))
                                                        <img src="{{ $item->url ?? asset('storage/media/' . $item->file_name) }}"
                                                            alt="{{ $item->name }}"
                                                            class="h-12 w-12 object-cover rounded border border-gray-200 dark:border-gray-700"
                                                            loading="lazy">
                                                    @else
                                                        <div
                                                            class="h-12 w-12 bg-gray-200 dark:bg-gray-700 rounded flex items-center justify-center border border-gray-200 dark:border-gray-700">
                                                            @if (str_starts_with($item->mime_type, 'video/'))
                                                                <iconify-icon icon="lucide:video"
                                                                    class="text-purple-400"></iconify-icon>
                                                            @elseif(str_starts_with($item->mime_type, 'application/pdf'))
                                                                <iconify-icon icon="lucide:file-text"
                                                                    class="text-red-400"></iconify-icon>
                                                            @else
                                                                <iconify-icon icon="lucide:file"
                                                                    class="text-gray-400"></iconify-icon>
                                                            @endif
                                                        </div>
                                                    @endif
                                                </div>
                                                <div>
                                                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                                                        {{ $item->name }}</p>
                                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                                        {{ $item->file_name }}
                                                    </p>
                                                </div>
                                            </td>
                                            <td class="table-td">
                                                <span
                                                    class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                                    {{ strtoupper(pathinfo($item->file_name, PATHINFO_EXTENSION)) }}
                                                </span>
                                            </td>
                                            <td class="table-td">
                                                {{ $item->human_readable_size }}
                                            </td>
                                            <td class="table-td">
                                                {{ $item->created_at->format('M d, Y') }}
                                            </td>
                                            <td class="table-td text-center">
                                                <div class="flex items-center justify-center gap-2">
                                                    @if (str_starts_with($item->mime_type, 'image/'))
                                                        <button
                                                            class="text-green-400 hover:text-green-600 dark:hover:text-green-300"
                                                            onclick="openImageModal('{{ $item->url ?? asset('storage/media/' . $item->file_name) }}', '{{ $item->name }}')"
                                                            title="{{ __('View Image') }}">
                                                            <iconify-icon icon="lucide:eye" class="text-sm"></iconify-icon>
                                                        </button>
                                                    @elseif (str_starts_with($item->mime_type, 'video/'))
                                                        <button
                                                            class="text-purple-400 hover:text-purple-600 dark:hover:text-purple-300"
                                                            onclick="openVideoModal('{{ $item->url ?? asset('storage/media/' . $item->file_name) }}', '{{ $item->name }}')"
                                                            title="{{ __('Play Video') }}">
                                                            <iconify-icon icon="lucide:play" class="text-sm"></iconify-icon>
                                                        </button>
                                                    @elseif (str_starts_with($item->mime_type, 'audio/'))
                                                        <button
                                                            class="text-emerald-400 hover:text-emerald-600 dark:hover:text-emerald-300"
                                                            onclick="openAudioModal('{{ $item->url ?? asset('storage/media/' . $item->file_name) }}', '{{ $item->name }}', '{{ $item->human_readable_size }}')"
                                                            title="{{ __('Play Audio') }}">
                                                            <iconify-icon icon="lucide:headphones" class="text-sm"></iconify-icon>
                                                        </button>
                                                    @elseif (str_starts_with($item->mime_type, 'application/pdf'))
                                                        <button
                                                            class="text-red-400 hover:text-red-600 dark:hover:text-red-300"
                                                            onclick="openPdfModal('{{ $item->url ?? asset('storage/media/' . $item->file_name) }}', '{{ $item->name }}')"
                                                            title="{{ __('View PDF') }}">
                                                            <iconify-icon icon="lucide:eye" class="text-sm"></iconify-icon>
                                                        </button>
                                                    @endif
                                                    <a href="{{ $item->url ?? asset('storage/media/' . $item->file_name) }}"
                                                        download="{{ $item->name }}"
                                                        class="text-blue-400 hover:text-blue-600 dark:hover:text-blue-300"
                                                        title="{{ __('Download') }}">
                                                        <iconify-icon icon="lucide:download" class="text-sm"></iconify-icon>
                                                    </a>
                                                    <button
                                                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                                                        onclick="copyToClipboard('{{ $item->url ?? asset('storage/media/' . $item->file_name) }}')"
                                                        title="{{ __('Copy URL') }}">
                                                        <iconify-icon icon="lucide:copy" class="text-sm"></iconify-icon>
                                                    </button>
                                                    @if (auth()->user()->can('media.delete'))
                                                        <button class="text-red-400 hover:text-red-600"
                                                            @click="showSingleDeleteModal({{ $item->id }})"
                                                            title="{{ __('Delete') }}">
                                                            <iconify-icon icon="lucide:trash"
                                                                class="text-sm"></iconify-icon>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <div class="text-center py-12">
                                <iconify-icon icon="lucide:image"
                                    class="text-6xl text-gray-300 dark:text-gray-600 mb-4 mx-auto"></iconify-icon>
                                <p class="text-gray-500 dark:text-gray-400 mb-4">{{ __('No media files found') }}</p>
                            </div>
                        @endif
                    </div>

                    <!-- Pagination -->
                    <div class="px-5 py-4">
                        {{ $media->links() }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Upload Modal -->
        @include('backend.pages.media.partials.upload-modal')

        <!-- Bulk Delete Modal -->
        @include('backend.pages.media.partials.bulk-delete-modal')
    </div>

    <!-- Image Modal -->
    <div id="imageModal" class="fixed inset-0 z-50 hidden bg-black bg-opacity-75"
        onclick="closeImageModal()">
        <div class="max-w-4xl max-h-[90vh] p-4" onclick="event.stopPropagation()">
            <img id="modalImage" src="" alt="" class="max-w-full max-h-full object-contain">
        </div>
        <button onclick="closeImageModal()" class="absolute top-4 right-4 text-white hover:text-gray-300">
            <iconify-icon icon="lucide:x" class="text-2xl"></iconify-icon>
        </button>
    </div>

    <!-- Video Modal -->
    <div id="videoModal" class="fixed inset-0 z-50 hidden bg-black bg-opacity-75"
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
        <button onclick="closeVideoModal()" class="absolute top-4 right-4 text-white hover:text-gray-300 z-10">
            <iconify-icon icon="lucide:x" class="text-2xl"></iconify-icon>
        </button>
    </div>

    <!-- Audio Modal -->
    <div id="audioModal" class="fixed inset-0 z-50 hidden bg-black bg-opacity-75"
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
        <button onclick="closeAudioModal()" class="absolute top-4 right-4 text-white hover:text-gray-300 z-10">
            <iconify-icon icon="lucide:x" class="text-2xl"></iconify-icon>
        </button>
    </div>

    <!-- PDF Modal -->
    <div id="pdfModal" class="fixed inset-0 z-50 hidden bg-black bg-opacity-75"
        onclick="closePdfModal()">
        <div class="max-w-7xl md:w-7xl max-h-[95vh] p-4" onclick="event.stopPropagation()">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden h-full">
                <!-- PDF Header -->
                <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 border-b border-gray-200 dark:border-gray-600 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <iconify-icon icon="lucide:file-text" class="text-red-500 text-xl"></iconify-icon>
                        <h3 id="modalPdfTitle" class="text-lg font-semibold text-gray-900 dark:text-white"></h3>
                    </div>
                    <div class="flex items-center gap-2">
                        <a id="pdfDownloadLink" href="#" download
                           class="px-3 py-1.5 bg-blue-500 text-white rounded hover:bg-blue-600 transition-colors text-sm flex items-center gap-2">
                            <iconify-icon icon="lucide:download" class="text-sm"></iconify-icon>
                            {{ __('Download') }}
                        </a>
                        <button type="button" onclick="closePdfModal()"
                                class="p-1.5 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                            <iconify-icon icon="lucide:x" class="text-xl"></iconify-icon>
                        </button>
                    </div>
                </div>

                <!-- PDF Viewer -->
                <div class="h-full bg-gray-100 dark:bg-gray-900">
                    <iframe id="modalPdfViewer"
                            src=""
                            class="w-full h-full border-0"
                            style="min-height: 600px;">
                    </iframe>
                </div>
            </div>
        </div>
        <button onclick="closePdfModal()" class="absolute top-4 right-4 text-white hover:text-gray-300 z-10">
            <iconify-icon icon="lucide:x" class="text-2xl"></iconify-icon>
        </button>
    </div>
@endsection

@push('scripts')
    <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                // Show success message
                if (window.showToast) {
                    window.showToast('success', '{{ __('Success') }}', '{{ __('URL copied to clipboard') }}');
                }
            });
        }

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

        // Close modal on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeImageModal();
            }
        });

        // Video Modal Functions
        function openVideoModal(url, title) {
            const modal = document.getElementById('videoModal');
            const video = document.getElementById('modalVideo');

            // Set video source
            video.src = url;
            video.load(); // Reload video with new source

            // Show modal
            modal.classList.remove('hidden');
            modal.classList.add('flex', 'items-center', 'justify-center');

            // Focus on video for keyboard controls
            setTimeout(() => video.focus(), 100);
        }

        function closeVideoModal() {
            const modal = document.getElementById('videoModal');
            const video = document.getElementById('modalVideo');

            // Pause and reset video
            video.pause();
            video.src = '';

            // Hide modal
            modal.classList.add('hidden');
            modal.classList.remove('flex', 'items-center', 'justify-center');
        }

        // Audio Modal Functions
        function openAudioModal(url, title, fileSize) {
            const modal = document.getElementById('audioModal');
            const audio = document.getElementById('modalAudio');
            const titleElement = document.getElementById('modalAudioTitle');
            const infoElement = document.getElementById('modalAudioInfo');

            // Set audio source and title
            audio.src = url;
            audio.load(); // Reload audio with new source
            titleElement.textContent = title || 'Audio File';

            // Add audio info
            infoElement.innerHTML = `
                <div>File: ${title || 'Audio File'}</div>
                ${fileSize ? `<div>Size: ${fileSize}</div>` : ''}
                <div>Format: ${url.split('.').pop().toUpperCase()}</div>
            `;

            // Show modal
            modal.classList.remove('hidden');
            modal.classList.add('flex', 'items-center', 'justify-center');

            // Focus on audio for keyboard controls
            setTimeout(() => audio.focus(), 100);
        }

        function closeAudioModal() {
            const modal = document.getElementById('audioModal');
            const audio = document.getElementById('modalAudio');

            // Pause and reset audio
            audio.pause();
            audio.src = '';

            // Hide modal
            modal.classList.add('hidden');
            modal.classList.remove('flex', 'items-center', 'justify-center');
        }

        // Enhanced keyboard navigation for modals
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                // Close any open modal
                closeImageModal();
                closeVideoModal();
                closeAudioModal();
                closePdfModal();
            }
        });

        // PDF Modal Functions
        function openPdfModal(url, title) {
            const modal = document.getElementById('pdfModal');
            const iframe = document.getElementById('modalPdfViewer');
            const titleElement = document.getElementById('modalPdfTitle');
            const downloadLink = document.getElementById('pdfDownloadLink');

            // Set PDF source and title
            iframe.src = url;
            titleElement.textContent = title || 'PDF Document';
            downloadLink.href = url;
            downloadLink.download = title || 'document.pdf';

            // Show modal
            modal.classList.remove('hidden');
            modal.classList.add('flex', 'items-center', 'justify-center');
        }

        function closePdfModal() {
            const modal = document.getElementById('pdfModal');
            const iframe = document.getElementById('modalPdfViewer');

            // Clear iframe source
            iframe.src = '';

            // Hide modal
            modal.classList.add('hidden');
            modal.classList.remove('flex', 'items-center', 'justify-center');
        }

        // Video/Audio/PDF preview button enhancements
        function handleVideoPreview(event, url, title) {
            event.preventDefault();
            event.stopPropagation();
            openVideoModal(url, title);
        }

        function handleAudioPreview(event, url, title, fileSize) {
            event.preventDefault();
            event.stopPropagation();
            openAudioModal(url, title, fileSize);
        }

        function handlePdfPreview(event, url, title) {
            event.preventDefault();
            event.stopPropagation();
            openPdfModal(url, title);
        }
    </script>
@endpush