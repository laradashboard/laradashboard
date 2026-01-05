@if(config('laradashboard.updates.enabled', true))
<button
    wire:click="checkForUpdates"
    wire:loading.attr="disabled"
    wire:target="checkForUpdates"
    class="btn-default"
    title="{{ __('Check for module updates from marketplace') }}"
>
    <span wire:loading.remove wire:target="checkForUpdates">
        <iconify-icon icon="lucide:refresh-cw" class="flex items-center text-base"></iconify-icon>
    </span>
    <span wire:loading wire:target="checkForUpdates">
        <iconify-icon icon="lucide:loader-2" class="flex items-center text-base animate-spin"></iconify-icon>
    </span>
    <span class="hidden sm:inline">{{ __('Check Updates') }}</span>
</button>
@endif