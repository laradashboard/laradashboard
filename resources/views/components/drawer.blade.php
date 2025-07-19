@props([
    'btn' => null,
    'isOpen' => false,
    'title' => null,
    'btnClass' => 'btn-primary',
    'btnIcon' => 'lucide:plus-circle',
    'width' => 'sm:w-120',
    'drawerId' => null,
    'headerBtn' => null,
    'headerBtnClass' => 'btn-default',
    'footerBtn' => null,
    'footerBtnClass' => 'btn-primary',
    'footerText' => null,
])

@php
    // Generate a consistent ID for this drawer
    $actualDrawerId = $drawerId ?? 'drawer-' . uniqid();
@endphp

<div x-data="{
    open: false,
    close() { this.open = false; },
    init() {
        // Register this drawer instance globally
        if (!window.LaraDrawers) window.LaraDrawers = {};
        const drawerId = '{{ $actualDrawerId }}';
        this.$el.setAttribute('data-drawer-id', drawerId);
        window.LaraDrawers[drawerId] = this;

        // Listen for direct open events with this drawer's ID
        window.addEventListener('open-drawer-' + drawerId, () => {
            console.log('Drawer event received:', drawerId);
            this.open = true;
        });
    }
}" class="relative" @keydown.escape.window="open = false" @close-drawer.window="close()"
    @open-drawer.window="open = true" :id="$id('drawer')" data-drawer-id="{{ $actualDrawerId }}">
    <!-- Trigger Button -->
    @if ($btn)
        <button type="button" @click="open = true" class="{{ $btnClass }}">
            @if ($btnIcon)
                <iconify-icon icon="{{ $btnIcon }}" class="mr-2"></iconify-icon>
            @endif
            {{ $btn }}
        </button>
    @endif

    <!-- Overlay Background -->
    <div x-show="open" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-300"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @click="close()"
        class="fixed inset-0 bg-gray-900/30 backdrop-blur-sm z-40">
    </div>

    <!-- Drawer Sidebar -->
    <div x-show="open" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-300" x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full" @click.stop
        class="fixed top-0 right-0 bottom-0 {{ $width }} max-w-md z-50 flex flex-col crm:bg-white dark:bg-gray-800 bg-white shadow-xl border-l border-gray-200 dark:border-gray-700">

        <!-- Header - Fixed at the top -->
        <div
            class="px-5 py-4 sm:px-6 sm:py-5 flex justify-between items-center border-b border-gray-200 dark:border-gray-700 flex-shrink-0">
            <div class="flex items-center">
                <h3 class="text-base font-medium text-gray-700 dark:text-white">
                    {{ $title ?? $btn }}
                </h3>
                @if ($headerBtn)
                    <button type="button" class="{{ $headerBtnClass }} ml-3">
                        {{ $headerBtn }}
                    </button>
                @endif
            </div>
            <button type="button" @click="close()"
                class="text-gray-500 hover:text-gray-700 dark:text-gray-300 dark:hover:text-gray-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </button>
        </div>

        <!-- Drawer Content - Scrollable area -->
        <div class="p-5 space-y-3 flex-1 overflow-y-auto" x-data="{}" x-bind="$data">
            {{ $slot }}
        </div>

        <!-- Fixed Footer -->
        <div class="px-5 py-4 sm:px-6 sm:py-3 border-t border-gray-200 dark:border-gray-700 flex-shrink-0">
            @if (isset($footer))
                {{ $footer }}
            @else
                <div class="flex justify-between items-center">
                    @if ($footerText)
                        <div class="text-sm text-gray-600 dark:text-gray-300">
                            {{ $footerText }}
                        </div>
                    @endif
                    <div class="ml-auto">
                        <button type="button" @click="close()" class="btn-default mr-2">
                            <iconify-icon icon="lucide:x-circle" class="mr-1"></iconify-icon>
                            {{ __('Cancel') }}
                        </button>
                        @if ($footerBtn)
                            <button type="button" class="{{ $footerBtnClass }}">
                                {{ $footerBtn }}
                            </button>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    (function() {
        if (typeof window.openDrawer !== 'function') {
            window.openDrawer = function(drawerId) {
                console.log('Opening drawer (local):', drawerId);

                if (window.LaraDrawers && window.LaraDrawers[drawerId]) {
                    console.log('Drawer found in registry');
                    window.LaraDrawers[drawerId].open = true;
                } else {
                    console.log('Drawer not found in registry, dispatching event');
                    window.dispatchEvent(new CustomEvent('open-drawer-' + drawerId));
                }
            };
        }
    })();
</script>
