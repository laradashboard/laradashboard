@php
    $enable_full_div_click = $enable_full_div_click ?? true;
@endphp

<div class="relative overflow-hidden border border-gray-200 dark:border-gray-700 rounded-lg bg-white px-4 pb-12 pt-5 shadow sm:px-6 sm:pt-6 dark:bg-gray-800 {{ $enable_full_div_click ? 'cursor-pointer hover:shadow-lg transition-shadow duration-300' : '' }}"
    @if($enable_full_div_click)
        onclick="window.location.href='{{ $url ?? '#' }}'"
    @endif
>
    <dt>
        <div class="absolute rounded-md bg-indigo-500 h-12 w-12 flex items-center justify-center" style="background: {{ $icon_bg ?? '#6366F1' }};">
            @if(!empty($icon))
                <iconify-icon icon="{{ $icon }}" class="size-6 text-white" height="24" width="24"></iconify-icon>
            @elseif(!empty($icon_svg))
                <img src="{{ $icon_svg }}" alt="" class="size-6 text-white">
            @else
                <svg class="size-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                </svg>
            @endif
        </div>
        <p class="ml-16 truncate text-sm font-medium text-gray-500 dark:text-gray-300">{{ $label }}</p>
    </dt>
    <dd class="ml-16 flex items-baseline pb-6 sm:pb-7">
        <p class="text-2xl font-semibold text-gray-800 dark:text-gray-100">{!! $value ?? 0 !!}</p>

        <div class="absolute inset-x-0 bottom-0 bg-gray-50 dark:bg-gray-700 px-4 py-4 sm:px-6">
            <div class="text-sm">
                <a href="{{ $url ?? '#' }}" class="font-medium flex items-center text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300">View all
                    <iconify-icon icon="heroicons:arrow-right" class="inline-block ml-1" width="16" height="16"></iconify-icon>
                    <span class="sr-only"> {{ $label }} stats</span></a>
            </div>
        </div>
    </dd>
</div>
