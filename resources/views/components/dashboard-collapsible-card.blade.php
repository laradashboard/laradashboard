@props([
    'title',
    'icon' => 'heroicons:square-3-stack-3d',
    'iconBg' => 'bg-gray-100 dark:bg-gray-700',
    'iconColor' => 'text-gray-600 dark:text-gray-400',
    'storageKey' => 'dashboard_card',
    'collapsedByDefault' => false,
])

@php
    $defaultCollapsed = $collapsedByDefault ? 'true' : 'false';
    $initLogic = $collapsedByDefault
        ? "localStorage.getItem('{$storageKey}_collapsed') !== 'false'"
        : "localStorage.getItem('{$storageKey}_collapsed') === 'true'";
@endphp

<div {{ $attributes->merge(['class' => 'rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm transition-all duration-200']) }}
     :class="collapsed ? 'p-3' : 'p-4'"
     x-data="{ collapsed: {{ $initLogic }} }"
     x-init="$watch('collapsed', val => localStorage.setItem('{{ $storageKey }}_collapsed', val))">
    {{-- Header --}}
    <div class="flex items-center justify-between cursor-pointer select-none"
         @click="collapsed = !collapsed">
        <div class="flex items-center gap-2">
            <div class="{{ $iconBg }} flex items-center justify-center rounded-lg transition-all duration-200"
                 :class="collapsed ? 'h-6 w-6' : 'h-7 w-7'">
                <iconify-icon icon="{{ $icon }}"
                              class="{{ $iconColor }} transition-all duration-200"
                              :class="collapsed ? 'text-sm' : 'text-base'"></iconify-icon>
            </div>
            <h3 class="font-semibold text-gray-800 dark:text-white"
                :class="collapsed ? 'text-sm' : 'text-lg'">
                {{ $title }}
            </h3>
        </div>
        <div class="flex items-center gap-2">
            {{-- Optional header actions slot --}}
            @if(isset($headerActions))
                <div x-show="!collapsed" x-cloak @click.stop>
                    {{ $headerActions }}
                </div>
            @endif
            <div class="p-1.5 rounded-lg pointer-events-none"
                 :title="collapsed ? '{{ __('Expand') }}' : '{{ __('Collapse') }}'">
                <iconify-icon icon="heroicons:chevron-down"
                              class="text-gray-500 dark:text-gray-400 transition-transform duration-200"
                              :class="{ 'rotate-180': collapsed }"></iconify-icon>
            </div>
        </div>
    </div>

    {{-- Content --}}
    <div x-show="!collapsed" x-collapse>
        <div class="pt-4">
            {{ $slot }}
        </div>
    </div>
</div>
