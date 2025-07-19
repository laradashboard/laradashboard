<!-- resources/views/components/json-modal.blade.php -->
<div id="json-modal-{{ $log->id }}"
    class="fixed inset-0 z-50 flex items-center justify-center hidden bg-opacity-50"
    style="background-color: #11182775">
    <div class="relative p-4 w-full max-w-4xl bg-white rounded-md shadow-lg dark:bg-gray-700 z-60">
        <!-- Modal Close Button -->
        <button type="button"
            class="absolute top-3 right-3 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-700 rounded-md text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white"
            data-modal-hide="json-modal-{{ $log->id }}">
            <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 14 14">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
            </svg>
            <span class="sr-only">{{ __('Close modal') }}</span>
        </button>

        <!-- Modal Content -->
        <h3 class="text-lg font-semibold text-gray-700 dark:text-white mb-4">{{ __('Action Log Data') }}</h3>

        <!-- Display pretty-printed JSON data -->
        <div class="space-y-3">
            <pre class="bg-gray-100 p-4 rounded-md text-gray-700 dark:text-white dark:bg-gray-800">
                {{ json_encode(json_decode($log->data), JSON_PRETTY_PRINT) }}
            </pre>
        </div>
    </div>
</div>
