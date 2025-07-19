@props([
    'id' => 'confirm-delete-modal',
    'title' => __('Delete Confirmation'),
    'content' => __('Are you sure you want to delete this item?'),
    'formId' => 'delete-form',
    'formAction' => '',
    'modalTrigger' => 'deleteModalOpen',
    'cancelButtonText' => __('No, cancel'),
    'confirmButtonText' => __('Yes, Confirm'),
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
        class="flex max-w-md flex-col gap-4 overflow-hidden rounded-md border border-outline border-gray-100 dark:border-gray-800 bg-white text-on-surface dark:border-outline-dark dark:bg-gray-700 dark:text-gray-300"
    >
        <div class="flex items-center justify-between border-b border-gray-100 px-4 py-2 dark:border-gray-800">
            <div class="flex items-center justify-center rounded-full bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400 p-1">
                <svg class="w-6 h-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 11V6m0 8h.01M19 10a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
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
            <p class="text-gray-500 dark:text-gray-300">{{ $content }}</p>
        </div>
        <div class="flex items-center justify-end gap-3 border-t border-gray-100 p-4 dark:border-gray-800">
            <form id="{{ $formId }}" action="{{ $formAction }}" method="POST">
                @method('DELETE')
                @csrf

                <button
                    type="button"
                    x-on:click="{{ $modalTrigger }} = false"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:text-white dark:focus:ring-gray-700"
                >
                    {{ $cancelButtonText }}
                </button>

                <button
                    type="submit"
                    class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-800 focus:outline-none focus:ring-2 focus:ring-red-300 dark:focus:ring-red-800"
                >
                    {{ $confirmButtonText }}
                </button>
            </form>
        </div>
    </div>
</div>
