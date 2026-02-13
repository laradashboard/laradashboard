<div class="inline-flex">
    <input type="file" wire:model="file" class="hidden" id="compact-file-upload-{{ $this->getId() }}">
    <button type="button"
        onclick="document.getElementById('compact-file-upload-{{ $this->getId() }}').click()"
        class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium text-gray-600 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors"
        wire:loading.attr="disabled"
        title="{{ __('Add attachment') }}">
        <span wire:loading.remove wire:target="file,upload">
            <iconify-icon icon="lucide:plus" class="text-xs"></iconify-icon>
            {{ __('Add') }}
        </span>
        <span wire:loading wire:target="file,upload" class="flex items-center">
            <iconify-icon icon="lucide:loader-2" class="text-xs animate-spin mr-1"></iconify-icon>
            {{ __('Uploading...') }}
        </span>
    </button>
</div>
