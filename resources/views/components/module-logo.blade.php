@props(['class' => ''])

<div {{ $attributes->merge(['class' => 'flex-shrink-0 ' . $class]) }}>
    @if($hasLogo())
        <img
            src="{{ $logoUrl }}"
            alt="{{ $alt }}"
            class="{{ $sizeClasses() }} rounded-lg object-cover"
        />
    @else
        <div class="{{ $sizeClasses() }} rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
            <iconify-icon
                icon="{{ $icon }}"
                class="{{ $iconSizeClass() }} text-gray-500 dark:text-gray-300"
            ></iconify-icon>
        </div>
    @endif
</div>
