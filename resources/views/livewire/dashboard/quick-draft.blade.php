<x-dashboard-collapsible-card
    :title="__('Quick Draft')"
    icon="heroicons:pencil-square"
    icon-bg="bg-brand-100 dark:bg-brand-900/30"
    icon-color="text-brand-600 dark:text-brand-400"
    storage-key="dashboard_quick_draft"
>
    <form wire:submit="save" class="space-y-3">
        {{-- Success Message --}}
        @if($showSuccess)
        <div class="flex items-center gap-2 p-2 rounded-lg bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400 text-xs"
             x-data="{ show: true }"
             x-init="setTimeout(() => { show = false; $wire.showSuccess = false }, 3000)"
             x-show="show"
             x-transition>
            <iconify-icon icon="heroicons:check-circle" class="text-base"></iconify-icon>
            {{ __('Draft saved successfully!') }}
        </div>
        @endif

        {{-- Title --}}
        <div>
            <label for="draft-title" class="form-label text-xs">
                {{ __('Title') }}
            </label>
            <input type="text"
                   id="draft-title"
                   wire:model="title"
                   placeholder="{{ __('Enter post title...') }}"
                   class="form-control text-sm py-1.5">
            @error('title')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- Content --}}
        <div>
            <label for="draft-content" class="form-label text-xs">
                {{ __('Content') }} <span class="text-gray-400 font-normal">({{ __('optional') }})</span>
            </label>
            <textarea id="draft-content"
                      wire:model="content"
                      rows="7"
                      placeholder="{{ __('Write your content here...') }}"
                      class="form-control-textarea text-sm py-1.5"></textarea>
            @error('content')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- Submit Button --}}
        <div class="flex justify-end pt-1">
            <button type="submit"
                    wire:loading.attr="disabled"
                    wire:loading.class="opacity-50 cursor-not-allowed"
                    class="btn-primary">
                <span wire:loading.remove wire:target="save">
                    <iconify-icon icon="heroicons:document-plus" class="flex items-center text-sm"></iconify-icon>
                </span>
                <span wire:loading wire:target="save">
                    <iconify-icon icon="heroicons:arrow-path" class="flex items-center text-sm animate-spin"></iconify-icon>
                </span>
                {{ __('Save Draft') }}
            </button>
        </div>
    </form>
</x-dashboard-collapsible-card>
