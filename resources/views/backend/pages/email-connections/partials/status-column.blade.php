@php
    $statusColors = [
        'green' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
        'red' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
        'yellow' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
        'gray' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-400',
    ];
    $colorClass = $statusColors[$connection->status_color] ?? $statusColors['gray'];
@endphp

<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $colorClass }}">
    @if($connection->status_color === 'green')
        <iconify-icon icon="lucide:check-circle" class="mr-1"></iconify-icon>
    @elseif($connection->status_color === 'red')
        <iconify-icon icon="lucide:x-circle" class="mr-1"></iconify-icon>
    @elseif($connection->status_color === 'yellow')
        <iconify-icon icon="lucide:alert-circle" class="mr-1"></iconify-icon>
    @else
        <iconify-icon icon="lucide:circle" class="mr-1"></iconify-icon>
    @endif
    {{ $connection->status_label }}
</span>
