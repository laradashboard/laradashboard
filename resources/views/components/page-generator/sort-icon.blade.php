@props([
    'field',
])

@php
    $currentSort = request('sort');
    $isAsc = $currentSort === $field;
    $isDesc = $currentSort === '-' . $field;
    $nextSort = $isAsc ? '-' . $field : $field;
@endphp

<a href="{{ request()->fullUrlWithQuery(['sort' => $nextSort]) }}" class="ml-1">
    @if($isAsc)
        <iconify-icon icon="lucide:sort-asc" class="text-primary"></iconify-icon>
    @elseif($isDesc)
        <iconify-icon icon="lucide:sort-desc" class="text-primary"></iconify-icon>
    @else
        <iconify-icon icon="lucide:arrow-up-down" class="text-gray-400"></iconify-icon>
    @endif
</a>