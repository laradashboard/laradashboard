@props(['btn' => 'Open Drawer', 'isOpen' => false, 'title' => null])

<div 
    x-data="{ 
        open: {{ $isOpen ? 'true' : 'false' }},
        close() { this.open = false; }
    }" 
    class="relative" 
    @keydown.escape.window="open = false"
    @close-drawer.window="close()"
    :id="$id('drawer')"
>
    <!-- Trigger Button -->
    <button type="button" @click="open = true" class="btn-primary">
        <i class="bi bi-plus-circle mr-2"></i>
        {{ $btn }}
    </button>

    <!-- Drawer Sidebar -->
    <div x-show="open"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="translate-x-full"
         class="fixed top-0 right-0 bottom-0 sm:w-96 max-w-md z-50 overflow-y-auto crm:bg-white dark:bg-gray-800 bg-white shadow-xl border-l border-gray-200 dark:border-gray-700">
        
        <!-- Header with built-in close button -->
        <div class="px-5 py-4 sm:px-6 sm:py-5 flex justify-between items-center border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-base font-medium text-gray-900 dark:text-white">
                {{ $title ?? $btn }}
            </h3>
            <button type="button" @click="close()" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        
        <!-- Drawer Content -->
        <div class="p-5 space-y-3 flex-1 overflow-y-auto" x-data="{}" x-bind="$data">
            {{ $slot }}
        </div>
    </div>
</div>
