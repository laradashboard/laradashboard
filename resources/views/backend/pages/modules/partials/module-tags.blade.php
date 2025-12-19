<div class="flex flex-wrap gap-1">
    @forelse ($module->tags ?? [] as $tag)
        <span class="badge">{{ $tag }}</span>
    @empty
        <span class="text-gray-400 dark:text-gray-500 text-sm">{{ __('N/A') }}</span>
    @endforelse
</div>
