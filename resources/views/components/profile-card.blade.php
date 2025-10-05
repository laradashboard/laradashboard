@props([
    'name' => '',
    'imageUrl' => null,
    'subtitle' => null,
    'extraInfo' => null,
    'link' => null,
    'size' => 'md',
    'tooltipTitle' => null,
    'tooltipId' => null,
])

@php
    // Extract initials from name (up to 2 characters)
    $initials = '';
    $nameParts = explode(' ', $name);
    
    if (count($nameParts) >= 2) {
        $initials = strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[1], 0, 1));
    } elseif (count($nameParts) === 1) {
        $initials = strtoupper(substr($nameParts[0], 0, min(2, strlen($nameParts[0]))));
    }
    
    // Define sizes
    $sizes = [
        'sm' => [
            'avatar' => 'w-8 h-8',
            'text' => 'text-xs',
            'name' => 'text-sm',
            'subtitle' => 'text-xs',
        ],
        'md' => [
            'avatar' => 'w-10 h-10',
            'text' => 'text-sm',
            'name' => 'text-sm',
            'subtitle' => 'text-xs',
        ],
        'lg' => [
            'avatar' => 'w-12 h-12',
            'text' => 'text-base',
            'name' => 'text-base',
            'subtitle' => 'text-sm',
        ],
    ];
    
    $sizeClasses = $sizes[$size] ?? $sizes['md'];
    
    // Generate consistent background color based on the name
    $colors = [
        'bg-blue-500', 'bg-green-500', 'bg-yellow-500', 'bg-red-500', 
        'bg-purple-500', 'bg-pink-500', 'bg-indigo-500', 'bg-teal-500'
    ];
    
    $colorIndex = crc32($name) % count($colors);
    $bgColor = $colors[$colorIndex];
@endphp

@if ($tooltipTitle)
    <x-tooltip title="{{ $tooltipTitle }}" position="top">
        <a data-tooltip-target="{{ $tooltipId }}" 
           href="{{ $link ?? '#' }}" 
           {{ $attributes->merge(['class' => 'flex items-center']) }}>
            <div class="flex-shrink-0 {{ $sizeClasses['avatar'] }} mr-3">
                @if ($imageUrl)
                    <img src="{{ $imageUrl }}" alt="{{ $name }}" class="{{ $sizeClasses['avatar'] }} rounded-full object-cover">
                @else
                    <div class="{{ $sizeClasses['avatar'] }} rounded-full {{ $bgColor }} flex items-center justify-center text-white {{ $sizeClasses['text'] }} font-medium">
                        {{ $initials }}
                    </div>
                @endif
            </div>
            <div class="flex flex-col gap-1 flex-1 min-w-0">
                <span class="{{ $sizeClasses['name'] }} font-medium text-gray-800 dark:text-white truncate">
                    {{ $name }}
                </span>
                @if ($subtitle)
                    <span class="{{ $sizeClasses['subtitle'] }} text-gray-500 dark:text-gray-300 truncate">
                        {{ $subtitle }}
                    </span>
                @endif
                @if ($extraInfo)
                    <span class="{{ $sizeClasses['subtitle'] }} text-gray-500 dark:text-gray-300 truncate">
                        {{ $extraInfo }}
                    </span>
                @endif
            </div>
        </a>
        
        @if ($tooltipId)
            <div id="{{ $tooltipId }}"
                 class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-md shadow-xs opacity-0 tooltip dark:bg-gray-700">
                {{ $tooltipTitle }}
                <div class="tooltip-arrow" data-popper-arrow></div>
            </div>
        @endif
    </x-tooltip>
@else
    <div {{ $attributes->merge(['class' => 'flex items-center']) }}>
        <div class="flex-shrink-0 {{ $sizeClasses['avatar'] }} mr-3">
            @if ($imageUrl)
                <img src="{{ $imageUrl }}" alt="{{ $name }}" class="{{ $sizeClasses['avatar'] }} rounded-full object-cover">
            @else
                <div class="{{ $sizeClasses['avatar'] }} rounded-full {{ $bgColor }} flex items-center justify-center text-white {{ $sizeClasses['text'] }} font-medium">
                    {{ $initials }}
                </div>
            @endif
        </div>
        <div class="flex flex-col gap-1 flex-1 min-w-0">
            @if ($link)
                <a href="{{ $link }}" class="{{ $sizeClasses['name'] }} font-medium text-gray-800 dark:text-white hover:text-primary dark:hover:text-primary truncate">
                    {{ $name }}
                </a>
            @else
                <span class="{{ $sizeClasses['name'] }} font-medium text-gray-800 dark:text-white truncate">
                    {{ $name }}
                </span>
            @endif
            
            @if ($subtitle)
                <span class="{{ $sizeClasses['subtitle'] }} text-gray-500 dark:text-gray-300 truncate">
                    {{ $subtitle }}
                </span>
            @endif
            
            @if ($extraInfo)
                <span class="{{ $sizeClasses['subtitle'] }} text-gray-500 dark:text-gray-300 truncate">
                    {{ $extraInfo }}
                </span>
            @endif
        </div>
    </div>
@endif
