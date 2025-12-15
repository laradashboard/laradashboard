@props([
    'label' => __('Actions'),
    'icon' => 'lucide:arrow-up-from-line',
    'items' => [],
    'position' => 'right', // 'left' or 'right'
    'dropdownAreaClass' => '',
])

<div x-data="{ isOpen: false }" class="relative">
    <button
        @click="isOpen = !isOpen"
        @click.outside="isOpen = false"
        type="button"
        class="btn-default bg-white gap-2 !border border-gray-300"
    >
        <iconify-icon icon="{{ $icon }}" width="18" height="18" class="text-gray-500 dark:text-gray-400"></iconify-icon>
        <span>{{ $label }}</span>
        <iconify-icon
            icon="lucide:chevron-down"
            width="16"
            height="16"
            class="text-gray-400 transition-transform duration-200"
            :class="isOpen ? 'rotate-180' : ''"
        ></iconify-icon>
    </button>

    <!-- Dropdown menu -->
    <div
        x-show="isOpen"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="absolute {{ $position === 'left' ? 'left-0' : 'right-0' }} mt-2 w-56 bg-white dark:bg-gray-800 rounded-lg shadow-lg ring-1 ring-gray-200 dark:ring-gray-700 z-50 overflow-hidden"
        x-cloak
    >
        <div class="py-1 {{ $dropdownAreaClass ?? '' }}">
            {{ $slot }}
        </div>
    </div>
</div>
