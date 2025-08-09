<div x-cloak x-show="bulkDeleteModalOpen"
     x-transition.opacity.duration.200ms
     x-trap.inert.noscroll="bulkDeleteModalOpen"
     x-on:keydown.esc.window="bulkDeleteModalOpen = false"
     x-on:click.self="bulkDeleteModalOpen = false"
     class="fixed inset-0 z-50 flex items-center justify-center bg-black/20 p-4 backdrop-blur-md"
     role="dialog"
     aria-modal="true">
    
    <div x-show="bulkDeleteModalOpen"
         x-transition:enter="transition ease-out duration-200 delay-100"
         x-transition:enter-start="opacity-0 scale-50"
         x-transition:enter-end="opacity-100 scale-100"
         class="flex max-w-md flex-col gap-4 overflow-hidden rounded-md border border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-700 dark:text-gray-300">
        
        <div class="flex items-center justify-between border-b border-gray-100 px-4 py-2 dark:border-gray-800">
            <div class="flex items-center justify-center rounded-full bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400 p-1">
                <iconify-icon icon="lucide:alert-triangle" class="w-6 h-6"></iconify-icon>
            </div>
            <h3 class="font-semibold tracking-wide text-gray-700 dark:text-white">
                {{ __('Delete Selected Media') }}
            </h3>
            <button x-on:click="bulkDeleteModalOpen = false"
                    aria-label="close modal"
                    class="text-gray-400 hover:bg-gray-200 hover:text-gray-700 rounded-md p-1 dark:hover:bg-gray-600 dark:hover:text-white">
                <iconify-icon icon="lucide:x" class="w-5 h-5"></iconify-icon>
            </button>
        </div>
        
        <div class="px-4 text-center">
            <p class="text-gray-500 dark:text-gray-300">
                {{ __('Are you sure you want to delete the selected media files?') }}
                {{ __('This action cannot be undone.') }}
            </p>
        </div>
        
        <div class="flex items-center justify-end gap-3 border-t border-gray-100 p-4 dark:border-gray-800">
            <form id="bulk-delete-form" action="{{ route('admin.media.bulk-delete') }}" method="POST">
                @method('DELETE')
                @csrf
                
                <template x-for="id in selectedMedia" :key="id">
                    <input type="hidden" name="ids[]" :value="id">
                </template>
                
                <button type="button" 
                        x-on:click="bulkDeleteModalOpen = false"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:text-white dark:focus:ring-gray-700">
                    {{ __('No, Cancel') }}
                </button>
                
                <button type="submit"
                        class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-800 focus:outline-none focus:ring-2 focus:ring-red-300 dark:focus:ring-red-800">
                    {{ __('Yes, Delete') }}
                </button>
            </form>
        </div>
    </div>
</div>
