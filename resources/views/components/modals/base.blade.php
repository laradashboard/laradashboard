@props([
    'id' => 'modal',
    'title' => '',
    'modalTrigger' => 'modalOpen',
    'size' => 'md', // sm, md, lg, xl, 2xl
])

@php
    $sizeClasses = [
        'sm' => 'md:max-w-sm',
        'md' => 'md:max-w-xl',
        'lg' => 'md:max-w-2xl',
        'xl' => 'md:max-w-4xl',
        '2xl' => 'md:max-w-6xl',
    ];
    $maxWidth = $sizeClasses[$size] ?? $sizeClasses['md'];
@endphp

<div x-cloak x-show="{{ $modalTrigger }}" x-transition.opacity.duration.200ms x-trap.inert.noscroll="{{ $modalTrigger }}"
    x-on:keydown.esc.window="{{ $modalTrigger }} = false" x-on:click.self="{{ $modalTrigger }} = false"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/20 p-4 backdrop-blur-md" role="dialog"
    aria-modal="true" aria-labelledby="{{ $id }}-title">
    <div x-show="{{ $modalTrigger }}"
        x-transition:enter="transition ease-out duration-200 delay-100 motion-reduce:transition-opacity"
        x-transition:enter-start="opacity-0 scale-50" x-transition:enter-end="opacity-100 scale-100"
        class="flex w-full {{ $maxWidth }} flex-col gap-4 overflow-hidden rounded-md border border-outline border-gray-100 dark:border-gray-800 bg-white text-on-surface dark:border-outline-dark dark:bg-gray-700 dark:text-gray-300">
        <div class="flex items-center justify-between border-b border-gray-100 px-4 py-2 dark:border-gray-800">
            <h3 id="{{ $id }}-title" class="font-semibold tracking-wide text-gray-700 dark:text-white">
                {{ $title }}
            </h3>
            <button x-on:click="{{ $modalTrigger }} = false" aria-label="close modal"
                class="text-gray-400 hover:bg-gray-200 hover:text-gray-700 rounded-md p-1 dark:hover:bg-gray-600 dark:hover:text-white">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true" stroke="currentColor"
                    fill="none" stroke-width="1.4" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div class="px-4 pb-4">
            {{ $slot }}
        </div>
    </div>
</div>
