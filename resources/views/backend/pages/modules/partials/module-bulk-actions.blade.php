<div class="flex items-center gap-2">
    {{-- Bulk Actions Dropdown (only shown when items are selected) --}}
    <div x-show="$wire.selectedItems.length > 0" x-data="{ bulkOpen: false }" class="relative">
        <button
            @click="bulkOpen = !bulkOpen"
            class="btn-secondary flex items-center justify-center gap-2 text-sm"
            type="button"
        >
            <iconify-icon icon="lucide:more-vertical"></iconify-icon>
            <span>{{ __('Bulk Actions') }} (<span x-text="$wire.selectedItems.length"></span>)</span>
            <iconify-icon icon="lucide:chevron-down"></iconify-icon>
        </button>

        <div
            x-show="bulkOpen"
            @click.outside="bulkOpen = false"
            x-transition
            class="absolute right-0 top-10 mt-2 w-56 rounded-md shadow bg-white dark:bg-gray-700 z-10 p-2"
        >
            <ul class="space-y-1">
                {{-- Activate Selected --}}
                <li>
                    <button
                        type="button"
                        wire:click="bulkActivate"
                        wire:loading.attr="disabled"
                        wire:target="bulkActivate"
                        @click="bulkOpen = false"
                        class="w-full cursor-pointer flex items-center gap-2 text-sm text-green-600 dark:text-green-400 hover:bg-green-50 dark:hover:bg-green-900/30 px-3 py-2 rounded transition-colors duration-200"
                    >
                        <span wire:loading.remove wire:target="bulkActivate">
                            <iconify-icon icon="lucide:toggle-right" class="text-base"></iconify-icon>
                        </span>
                        <span wire:loading wire:target="bulkActivate">
                            <iconify-icon icon="lucide:loader-2" class="text-base animate-spin"></iconify-icon>
                        </span>
                        {{ __('Activate Selected') }}
                    </button>
                </li>

                {{-- Deactivate Selected --}}
                <li>
                    <button
                        type="button"
                        wire:click="bulkDeactivate"
                        wire:loading.attr="disabled"
                        wire:target="bulkDeactivate"
                        @click="bulkOpen = false"
                        class="w-full cursor-pointer flex items-center gap-2 text-sm text-amber-600 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-900/30 px-3 py-2 rounded transition-colors duration-200"
                    >
                        <span wire:loading.remove wire:target="bulkDeactivate">
                            <iconify-icon icon="lucide:toggle-left" class="text-base"></iconify-icon>
                        </span>
                        <span wire:loading wire:target="bulkDeactivate">
                            <iconify-icon icon="lucide:loader-2" class="text-base animate-spin"></iconify-icon>
                        </span>
                        {{ __('Deactivate Selected') }}
                    </button>
                </li>

                <li class="border-t border-gray-200 dark:border-gray-600 my-1"></li>

                {{-- Delete Selected --}}
                <li>
                    <button
                        type="button"
                        @click="bulkOpen = false; bulkDeleteModalOpen = true"
                        class="w-full cursor-pointer flex items-center gap-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30 px-3 py-2 rounded transition-colors duration-200"
                    >
                        <iconify-icon icon="lucide:trash" class="text-base"></iconify-icon>
                        {{ __('Delete Selected') }}
                    </button>
                </li>
            </ul>
        </div>
    </div>
</div>
