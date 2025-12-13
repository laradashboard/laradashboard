@props(['enableLivewire' => false, 'placeholder' => null])

@if($enableLivewire ?? false)
    <div class="relative flex items-center justify-center min-w-full md:min-w-[280px]" wire:ignore.self>
        <span class="pointer-events-none absolute left-4 flex">
            <iconify-icon icon="lucide:search" class="text-gray-500 dark:text-gray-400" width="20" height="20"></iconify-icon>
        </span>
        <input
            id="search-input"
            type="text"
            wire:model.live="search"
            placeholder="{{ $placeholder ?? __('Search...') }}"
            class="form-control !pl-12 !pr-14"
            autocomplete="off"
        />
        <button
            id="search-button"
            class="absolute right-2.5 top-1/2 inline-flex -translate-y-1/2 items-center gap-0.5 rounded-md border border-gray-200 bg-gray-50 px-[7px] py-[4.5px] text-xs -tracking-[0.2px] text-gray-500 dark:border-gray-800 dark:bg-white/[0.03] dark:text-gray-300"
            aria-label="{{ __('Search') }}"
            type="button"
            title="{{ __('Search') }}"
            tabindex="-1"
            disabled
        >
            <iconify-icon icon="lucide:command" class="mr-1" width="16" height="16"></iconify-icon>
            <span>K</span>
        </button>
    </div>
@else
    <form
        action="{{ url()->current() }}"
        method="GET"
        class="flex items-center"
        name="search"
    >
        @foreach(request()->except('search') as $key => $value)
            @if(is_array($value))
                @foreach($value as $subKey => $subValue)
                    <input type="hidden" name="{{ $key }}[{{ $subKey }}]" value="{{ is_array($subValue) ? json_encode($subValue) : $subValue }}">
                @endforeach
            @else
                <input type="hidden" name="{{ $key }}" value="{{ is_array($value) ? json_encode($value) : $value }}">
            @endif
        @endforeach

        <div class="relative flex items-center justify-center min-w-auto md:min-w-[280px]">
            <span class="pointer-events-none absolute left-4 flex">
                <iconify-icon icon="lucide:search" class="text-gray-500 dark:text-gray-400" width="20" height="20"></iconify-icon>
            </span>
            <input
                id="search-input"
                name="search"
                value="{{ request('search') }}"
                type="text"
                placeholder="{{ $placeholder ?? __('Search...') }}"
                class="form-control !pl-12 !pr-14"
            />

            <button
                id="search-button"
                class="absolute right-2.5 top-1/2 inline-flex -translate-y-1/2 items-center gap-0.5 rounded-md border border-gray-200 bg-gray-50 px-[7px] py-[4.5px] text-xs -tracking-[0.2px] text-gray-500 dark:border-gray-800 dark:bg-white/[0.03] dark:text-gray-300"
                aria-label="{{ __('Search') }}"
                type="submit"
                title="{{ __('Search') }}"
            >
                <span> ⌘ </span>
                <span> K </span>
            </button>
        </div>
    </form>
@endif
