<div x-data="{ open: false, showConfirm: false }" class="relative">
    <!-- Dropdown Trigger -->
    <button
        @click="open = !open"
        type="button"
        class="btn-secondary flex items-center gap-1 text-sm"
        title="{{ __('More actions') }}"
    >
        <iconify-icon icon="lucide:more-vertical"></iconify-icon>
    </button>

    <!-- Dropdown Menu -->
    <div
        x-show="open"
        @click.outside="open = false"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white dark:bg-gray-700 ring-1 ring-gray-200 ring-opacity-5 z-50"
    >
        <div class="py-1">
            <button
                @click="open = false; showConfirm = true"
                type="button"
                class="w-full text-left px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 flex items-center gap-2"
            >
                <iconify-icon icon="lucide:trash-2"></iconify-icon>
                {{ __('Clean All Logs') }}
            </button>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div
        x-cloak
        x-show="showConfirm"
        x-transition.opacity.duration.200ms
        x-trap.inert.noscroll="showConfirm"
        @keydown.esc.window="showConfirm = false"
        @click.self="showConfirm = false"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/20 p-4 backdrop-blur-md"
        role="dialog"
        aria-modal="true"
    >
        <div
            x-show="showConfirm"
            x-transition:enter="transition ease-out duration-200 delay-100"
            x-transition:enter-start="opacity-0 scale-50"
            x-transition:enter-end="opacity-100 scale-100"
            class="flex max-w-md flex-col gap-4 overflow-hidden rounded-md border border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-700"
        >
            <!-- Header -->
            <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 px-4 py-3">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center rounded-full bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400 p-2">
                        <iconify-icon icon="lucide:alert-triangle" class="text-xl"></iconify-icon>
                    </div>
                    <h3 class="font-semibold text-gray-700 dark:text-white">
                        {{ __('Clean All Action Logs') }}
                    </h3>
                </div>
                <button
                    @click="showConfirm = false"
                    type="button"
                    class="text-gray-400 hover:bg-gray-200 hover:text-gray-700 rounded-md p-1 dark:hover:bg-gray-600 dark:hover:text-white flex items-center"
                >
                    <iconify-icon icon="lucide:x" class="text-lg"></iconify-icon>
                </button>
            </div>

            <!-- Body -->
            <div class="px-4 text-center">
                <p class="text-gray-600 dark:text-gray-300">
                    {{ __('Are you sure you want to delete all action logs?') }}
                </p>
                <p class="text-sm text-red-500 dark:text-red-400 mt-2">
                    {{ __('This action cannot be undone.') }}
                </p>
            </div>

            <!-- Footer -->
            <div class="flex items-center justify-end gap-3 border-t border-gray-100 dark:border-gray-800 p-4">
                <button
                    @click="showConfirm = false"
                    type="button"
                    class="btn-default"
                >
                    {{ __('Cancel') }}
                </button>
                <form action="{{ route('admin.actionlog.clean') }}" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button
                        type="submit"
                        class="btn-danger flex items-center gap-2"
                    >
                        <iconify-icon icon="lucide:trash-2"></iconify-icon>
                        {{ __('Yes, Clean All') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
