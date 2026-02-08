@props(['url', 'filename'])

<a
    href="{{ $url }}"
    target="_blank"
    download
    class="inline-flex items-center gap-1.5 text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300"
>
    <iconify-icon icon="lucide:download" class="text-base"></iconify-icon>
    <span class="truncate max-w-[150px]">{{ $filename }}</span>
</a>
