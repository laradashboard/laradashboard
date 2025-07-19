@props([
    'title',
    'id' => null,
    'defaultOpen' => false,
    'headerClass' => '',
    'contentClass' => ''
])

<div class="rounded-md border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]"
     x-data="{ open: {{ $defaultOpen ?? false ? 'true' : 'false' }} }">
    <button type="button"
            @click="open = !open"
            class="flex w-full items-center justify-between p-5 text-left {{ $headerClass ?? '' }}">
        <h3 class="text-lg font-medium text-gray-700 dark:text-white">{{ $title ?? __('Advanced Fields') }}</h3>
        <svg class="h-5 w-5 transform transition-transform duration-200 dark:text-gray-300"
             :class="{ 'rotate-180': open }"
             fill="none"
             stroke="currentColor"
             viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
    </button>

    <div x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 transform scale-y-95"
         x-transition:enter-end="opacity-100 transform scale-y-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 transform scale-y-100"
         x-transition:leave-end="opacity-0 transform scale-y-95"
         class="border-t border-gray-100 dark:border-gray-800">
        <div class="p-5 {{ $contentClass ?? '' }}">
            @yield('collapsible-content')
        </div>
    </div>
</div>
