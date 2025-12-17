<div x-show="$wire.selectedItems.length > 0" class="flex items-center gap-2">
    <button
        wire:click="bulkActivate"
        wire:loading.attr="disabled"
        wire:target="bulkActivate"
        class="btn-success text-sm flex items-center gap-2"
    >
        <span wire:loading.remove wire:target="bulkActivate">
            <iconify-icon icon="lucide:toggle-right"></iconify-icon>
        </span>
        <span wire:loading wire:target="bulkActivate">
            <iconify-icon icon="lucide:loader-2" class="animate-spin"></iconify-icon>
        </span>
        {{ __('Activate') }}
    </button>

    <button
        wire:click="bulkDeactivate"
        wire:loading.attr="disabled"
        wire:target="bulkDeactivate"
        class="btn-warning text-sm flex items-center gap-2"
    >
        <span wire:loading.remove wire:target="bulkDeactivate">
            <iconify-icon icon="lucide:toggle-left"></iconify-icon>
        </span>
        <span wire:loading wire:target="bulkDeactivate">
            <iconify-icon icon="lucide:loader-2" class="animate-spin"></iconify-icon>
        </span>
        {{ __('Deactivate') }}
    </button>
</div>
