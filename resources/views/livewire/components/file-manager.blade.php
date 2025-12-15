<x-card>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white">{{ __('Attachments') }}</h3>
            <div>
                <input type="file" wire:model="file" class="hidden" id="file-upload-{{ $this->getId() }}">
                <button type="button" onclick="document.getElementById('file-upload-{{ $this->getId() }}').click()"
                    class="btn btn-primary transition inline-flex items-center"
                    wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="file,upload">
                        <iconify-icon icon="lucide:plus" class="mr-2"></iconify-icon>
                        {{ __('Add File') }}
                    </span>
                    <span wire:loading wire:target="file,upload" class="flex items-center">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        {{ __('Uploading...') }}
                    </span>
                </button>
            </div>
        </div>
    </x-slot>


    @if ($file)
        <div wire:poll.1s="upload" class="mb-4 p-3 bg-blue-50 dark:bg-blue-900/20 rounded border">
            <div class="text-sm text-blue-800 dark:text-blue-200">
                {{ __('Selected:') }} {{ $file->getClientOriginalName() }}
            </div>
        </div>
    @endif

    <div class="space-y-3">
        @forelse($files as $file)
            <div class="flex items-center p-1 rounded-lg bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 group hover:border-primary transition-all">
                @php
                    $fileName = is_array($file) ? $file['name'] : $file;
                    $filePath = is_array($file) ? $file['path'] : $file;
                    $fileUrl = is_array($file)
                        ? $file['url'] ?? asset('storage/' . $filePath)
                        : asset('storage/' . $file);
                    $fileId = is_array($file) ? $file['id'] ?? $filePath : $file;
                    $isImage = in_array(strtolower(pathinfo($fileName, PATHINFO_EXTENSION)), [
                        'jpg',
                        'jpeg',
                        'png',
                        'gif',
                        'webp',
                    ]);
                @endphp

                <div class="w-10 h-10 flex items-center justify-center mr-3 flex-shrink-0">
                    @if ($isImage)
                        <img src="{{ $fileUrl }}" alt="{{ $fileName }}" class="w-10 h-10 object-cover rounded">
                    @else
                        <svg class="w-8 h-8 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    @endif
                </div>

                <div class="flex-1 min-w-0">
                    <div class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $fileName }}</div>
                    @if (is_array($file) && isset($file['size']))
                        <div class="text-xs text-gray-500 dark:text-gray-400">
                            {{ number_format($file['size'] / 1024, 1) }} KB</div>
                    @endif
                </div>

                <div class="flex items-center space-x-2 ml-3">
                    <a href="{{ $fileUrl }}" target="_blank"
                        class="text-primary hover:opacity-90"
                        title="{{ __('View') }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </a>

                    <a href="{{ $fileUrl }}" download="{{ $fileName }}"
                        class="text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-300"
                        title="{{ __('Download') }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </a>

                    @if ($isDeletable)
                        <button wire:click="deleteFile('{{ $fileId }}')"
                            class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300"
                            title="{{ __('Delete') }}">
                            <iconify-icon icon="heroicons:trash" class="w-5 h-5"></iconify-icon>
                        </button>
                    @endif
                </div>
            </div>
        @empty
            <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                    <path
                        d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <p class="mt-2">{{ __('No files uploaded yet') }}</p>
            </div>
        @endforelse
    </div>
</x-card>
