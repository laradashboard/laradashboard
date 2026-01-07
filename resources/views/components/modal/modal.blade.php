<div class="relative z-50">
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center"
        style="display: none;"
        @keydown.escape.window="open = false"
    >
        <div
            @click.away="open = false"
            class="relative bg-white dark:bg-gray-900 rounded-lg shadow-lg max-w-lg w-full mx-4"
        >
            @isset($header)
                <div class="px-6 py-4 border-b border-gray-200 font-semibold flex justify-between items-center {{ $headerClass ?? '' }}">
                    {{ $header }}
                    <button type="button" class="btn-outline-secondary p-1 flex items-center justify-center w-6 h-6" @click="open=false" title="{{ __('Close') }}">
                        <iconify-icon icon="mdi:close" class="flex w-5 h-5" />
                    </button>
                </div>
            @endisset

            <!-- If no header, still need to show a close button -->
            @if(!isset($header))
                <button type="button" class="absolute right-3 top-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200" @click="open=false" title="{{ __('Close') }}">
                    <iconify-icon icon="mdi:close" class="w-5 h-5" />
                </button>
            @endif

            <div class="px-6 py-4 {{ $bodyClass ?? '' }}">
                {{ $slot }}
            </div>

            @isset($footer)
                <div class="px-6 py-4 border-t border-gray-200 flex justify-start gap-4 items-center {{ $footerClass ?? '' }}">
                    {{ $footer }}
                </div>
            @endisset
        </div>
    </div>
</div>
