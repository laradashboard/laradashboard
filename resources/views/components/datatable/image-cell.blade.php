@props(['url', 'alt' => ''])

<a href="{{ $url }}" target="_blank" class="block">
    <img
        src="{{ $url }}"
        alt="{{ $alt }}"
        class="h-10 w-10 rounded-lg object-cover ring-1 ring-gray-200 dark:ring-gray-700 hover:ring-primary-500 transition-all"
    >
</a>
