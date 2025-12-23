@props(['enableLivewire' => false, 'placeholder' => null])

@if($enableLivewire ?? false)
    <div class="relative flex items-center justify-center min-w-full md:min-w-[280px]"
        wire:ignore.self
        x-data="{
            searchValue: $wire.search || '',
            isMac: navigator.platform.toUpperCase().indexOf('MAC') >= 0
        }"
        x-init="
            $watch('$wire.search', value => searchValue = value || '');
            window.addEventListener('keydown', (e) => {
                if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
                    e.preventDefault();
                    $refs.searchInput.focus();
                }
            });
        "
    >
        <span class="pointer-events-none absolute left-4 flex">
            <iconify-icon icon="lucide:search" class="text-gray-500 dark:text-gray-400" width="20" height="20"></iconify-icon>
        </span>
        <input
            id="search-input"
            x-ref="searchInput"
            type="text"
            wire:model.live="search"
            x-model="searchValue"
            placeholder="{{ $placeholder ?? __('Search...') }}"
            class="form-control !pl-12 !pr-14"
            autocomplete="off"
        />

        {{-- Clear button - shows when input has value --}}
        <button
            x-show="searchValue.length > 0"
            x-cloak
            @click="$wire.set('search', ''); searchValue = ''"
            class="absolute right-2.5 top-1/2 inline-flex -translate-y-1/2 items-center justify-center rounded-full p-1 text-gray-400 transition-colors hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-700 dark:hover:text-gray-300"
            aria-label="{{ __('Clear search') }}"
            type="button"
            title="{{ __('Clear search') }}"
        >
            <iconify-icon icon="lucide:x" width="18" height="18"></iconify-icon>
        </button>

        {{-- Keyboard shortcut indicator - shows when input is empty --}}
        <span
            x-show="searchValue.length === 0"
            class="absolute right-2.5 top-1/2 inline-flex -translate-y-1/2 items-center gap-0.5 rounded-md border border-gray-200 bg-gray-50 px-2 py-[4.5px] text-xs -tracking-[0.2px] text-gray-500 dark:border-gray-800 dark:bg-white/3 dark:text-gray-300"
        >
            <template x-if="isMac">
                <iconify-icon icon="lucide:command" class="mr-0.5" width="14" height="14"></iconify-icon>
            </template>
            <template x-if="!isMac">
                <span class="mr-0.5 text-[11px]">Ctrl</span>
            </template>
            <span>K</span>
        </span>
    </div>
@else
    <form
        action="{{ url()->current() }}"
        method="GET"
        class="flex items-center"
        name="search"
        x-data="{
            searchValue: '{{ request('search') }}',
            isMac: navigator.platform.toUpperCase().indexOf('MAC') >= 0
        }"
        x-init="
            window.addEventListener('keydown', (e) => {
                if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
                    e.preventDefault();
                    $refs.searchInput.focus();
                }
            });
        "
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

        <div class="relative flex items-center justify-center min-w-full md:min-w-80 lg:min-w-96 xl:min-w-130 2xl:min-w-150">
            <span class="pointer-events-none absolute left-4 flex">
                <iconify-icon icon="lucide:search" class="text-gray-500 dark:text-gray-400" width="20" height="20"></iconify-icon>
            </span>
            <input
                id="search-input"
                x-ref="searchInput"
                name="search"
                type="text"
                x-model="searchValue"
                placeholder="{{ $placeholder ?? __('Search...') }}"
                class="form-control pl-12! pr-14!"
            />

            {{-- Clear button - shows when input has value --}}
            <button
                x-show="searchValue.length > 0"
                x-cloak
                @click.prevent="searchValue = ''; $nextTick(() => $el.closest('form').submit())"
                class="absolute right-2.5 top-1/2 inline-flex -translate-y-1/2 items-center justify-center rounded-full p-1 text-gray-400 transition-colors hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-700 dark:hover:text-gray-300"
                aria-label="{{ __('Clear search') }}"
                type="button"
                title="{{ __('Clear search') }}"
            >
                <iconify-icon icon="lucide:x" width="18" height="18"></iconify-icon>
            </button>

            {{-- Keyboard shortcut indicator - shows when input is empty --}}
            <button
                x-show="searchValue.length === 0"
                class="absolute right-2.5 top-1/2 inline-flex -translate-y-1/2 items-center gap-0.5 rounded-md border border-gray-200 bg-gray-50 px-1.75 py-[4.5px] text-xs -tracking-[0.2px] text-gray-500 dark:border-gray-800 dark:bg-white/3 dark:text-gray-300"
                aria-label="{{ __('Search') }}"
                type="submit"
                title="{{ __('Search') }}"
            >
                <template x-if="isMac">
                    <iconify-icon icon="lucide:command" class="mr-0.5" width="14" height="14"></iconify-icon>
                </template>
                <template x-if="!isMac">
                    <span class="mr-0.5 text-[11px]">Ctrl</span>
                </template>
                <span>K</span>
            </button>
        </div>
    </form>
@endif
