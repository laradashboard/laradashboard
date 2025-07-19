@props([
    'id' => 'error-message-modal',
    'title' => __('Error'),
    'content' => null,
    'modalTrigger' => 'errorModalOpen',
    'closeButtonText' => __('Close'),
])

<div
    x-cloak
    x-show="{{ $modalTrigger }}"
    x-transition.opacity.duration.200ms
    x-trap.inert.noscroll="{{ $modalTrigger }}"
    x-on:keydown.esc.window="{{ $modalTrigger }} = false"
    x-on:click.self="{{ $modalTrigger }} = false"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/20 p-4 backdrop-blur-md"
    role="dialog"
    aria-modal="true"
    aria-labelledby="{{ $id }}-title"
>
    <div
        x-show="{{ $modalTrigger }}"
        x-transition:enter="transition ease-out duration-200 delay-100 motion-reduce:transition-opacity"
        x-transition:enter-start="opacity-0 scale-50"
        x-transition:enter-end="opacity-100 scale-100"
        class="flex max-w-md flex-col gap-4 overflow-hidden rounded-md border border-outline bg-white text-on-surface dark:border-outline-dark dark:bg-gray-700 dark:text-gray-300"
    >
        <div class="flex items-center justify-between border-b border-gray-100 px-4 py-2 dark:border-gray-800">
            <div class="flex items-center justify-center rounded-full bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400 p-1">
                <svg class="w-6 h-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <h3 id="{{ $id }}-title" class="font-semibold tracking-wide text-gray-700 dark:text-white">{{ $title }}</h3>
            <button
                x-on:click="{{ $modalTrigger }} = false"
                aria-label="close modal"
                class="text-gray-400 hover:bg-gray-200 hover:text-gray-700 rounded-md p-1 dark:hover:bg-gray-600 dark:hover:text-white"
            >
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="1.4" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="px-4 text-center">
            <p x-text="errorMessage" class="text-gray-500 dark:text-gray-300"></p>
        </div>
        <div class="flex items-center justify-end gap-3 border-t border-gray-100 p-4 dark:border-gray-800">
            <button
                type="button"
                x-on:click="{{ $modalTrigger }} = false"
                class="px-4 py-2 text-sm font-medium text-white bg-primary rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300 dark:focus:ring-blue-800"
            >
                {{ $closeButtonText }}
            </button>
        </div>
    </div>
</div>
