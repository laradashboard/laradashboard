@php
    // Default core quick links
    $defaultLinks = [
        [
            'label' => __('Visit Site'),
            'url' => config('app.url'),
            'icon' => 'lucide:globe',
            'target' => '_blank',
        ],
        [
            'label' => __('Marketplace'),
            'url' => 'https://laradashboard.com/marketplace',
            'icon' => 'lucide:store',
            'target' => '_blank',
        ],
    ];

    // Allow modules to add more links via filter hook
    $quickLinks = Hook::applyFilters(AdminFilterHook::QUICK_LINKS_DROPDOWN, $defaultLinks);
@endphp

@if (count($quickLinks) > 0)
    <div class="relative" x-data="{ quickLinksOpen: false }" @click.outside="quickLinksOpen = false">
        <x-tooltip title="{{ __('Quick Links') }}" position="bottom">
            <button
                @click="quickLinksOpen = !quickLinksOpen"
                class="hover:text-dark-900 relative flex items-center justify-center rounded-full text-gray-700 transition-colors hover:bg-gray-100 hover:text-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white p-2"
                aria-label="{{ __('Quick Links') }}"
            >
                <iconify-icon icon="lucide:link" width="22" height="22"></iconify-icon>
            </button>
        </x-tooltip>

        <div
            x-show="quickLinksOpen"
            x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="absolute left-0 mt-2 w-56 rounded-md border bg-white dark:bg-gray-700 border-gray-200 dark:border-gray-800 shadow-lg z-100"
            style="display: none;"
        >
            <div class="py-1">
                <div class="px-3 py-2 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider border-b border-gray-200 dark:border-gray-600">
                    {{ __('Quick Links') }}
                </div>
                @foreach ($quickLinks as $link)
                    @php
                        $hasPermission = empty($link['permission']) || auth()->user()?->can($link['permission']);
                    @endphp
                    @if ($hasPermission)
                        <a
                            href="{{ $link['url'] }}"
                            target="{{ $link['target'] ?? '_blank' }}"
                            class="group flex items-center gap-3 px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-600"
                            @click="quickLinksOpen = false"
                        >
                            <iconify-icon
                                icon="{{ $link['icon'] ?? 'lucide:external-link' }}"
                                width="18"
                                height="18"
                                class="text-gray-500 group-hover:text-gray-700 dark:text-gray-400 dark:group-hover:text-gray-200"
                            ></iconify-icon>
                            <span>{{ $link['label'] }}</span>
                        </a>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
@endif
