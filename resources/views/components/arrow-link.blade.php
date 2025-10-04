@php
$text = $text ?? '';
$href = $href ?? '#';
$color = $color ?? 'blue';
$underline = $underline ?? false;

$colors = [
    'blue' => 'text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-200',
    'primary' => 'text-primary hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-200',
    'green' => 'text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-200',
    'red' => 'text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-200',
    'gray' => 'text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200',
    'purple' => 'text-purple-600 hover:text-purple-800 dark:text-purple-400 dark:hover:text-purple-200',
];

$colorClass = $colors[$color] ?? $colors['blue'];
$underlineClass = $underline ? 'hover:underline' : 'no-underline';
@endphp

<a href="{{ $href }}" class="inline-flex items-center gap-1.5 {{ $colorClass }} {{ $underlineClass }} transition-colors">
    <span>{{ $text ?: $slot }}</span>
    <iconify-icon icon="lucide:arrow-right" class="text-sm"></iconify-icon>
</a>