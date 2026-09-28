@props([
    'id',
    'name',
    'options' => [],
    'selected' => null,
    'enableLivewire' => true,
    'route' => '',
    'dropUp' => false,
    'triggerClass' => '',
])

@php
    $selectedOption = collect($options)->first(fn ($option) => (string) $option['value'] === (string) $selected);
@endphp

<div
    {{ $attributes->class('relative') }}
    x-data="{ open: false }"
    x-on:keydown.escape="open = false"
    x-on:click.outside="open = false"
>
    <button
        type="button"
        id="{{ $id }}"
        x-ref="trigger"
        class="form-control-combobox {{ $triggerClass }}"
        aria-haspopup="listbox"
        aria-controls="{{ $id }}-listbox"
        :aria-expanded="open.toString()"
        x-on:click="open = !open; if (open) $nextTick(() => $refs.listbox.scrollIntoView({ block: 'nearest' }))"
        x-on:keydown.down.prevent="open = true; $nextTick(() => $focus.within($refs.listbox).first())"
    >
        <span class="text-sm font-normal text-left truncate">{!! $selectedOption['label'] ?? '' !!}</span>
        <iconify-icon
            icon="mdi:chevron-down"
            class="text-2xl text-gray-400 dark:text-gray-300 transition-transform duration-200"
            :class="open && 'rotate-180'"
            aria-hidden="true"
            noobserver
        ></iconify-icon>
    </button>

    <ul
        id="{{ $id }}-listbox"
        x-ref="listbox"
        x-cloak
        x-show="open"
        x-transition
        role="listbox"
        aria-labelledby="{{ $id }}"
        x-on:keydown.down.prevent="$focus.wrap().next()"
        x-on:keydown.up.prevent="$focus.wrap().previous()"
        @class([
            'absolute z-50 left-0 w-full max-h-60 overflow-y-auto rounded-md border border-gray-200 bg-white py-1 shadow-lg dark:border-gray-700 dark:bg-gray-900',
            'bottom-full mb-1' => $dropUp,
            'top-full mt-1' => ! $dropUp,
        ])
    >
        @foreach($options as $option)
            @php
                $isSelected = (string) $option['value'] === (string) $selected;
            @endphp
            <li
                role="option"
                tabindex="0"
                aria-selected="{{ $isSelected ? 'true' : 'false' }}"
                class="px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 focus:bg-gray-100 focus:outline-none dark:text-white/90 dark:hover:bg-gray-800 dark:focus:bg-gray-800 cursor-pointer flex items-center justify-between gap-2"
                @if($enableLivewire)
                    wire:click="$set(@js($name), @js($option['value']))"
                @else
                    onclick="window.location.href = @js($route.'?'.$name.'='.urlencode((string) $option['value']))"
                @endif
                x-on:click="open = false; $refs.trigger.focus()"
                x-on:keydown.enter.prevent="$el.click()"
                x-on:keydown.space.prevent="$el.click()"
            >
                <span @class(['font-medium' => $isSelected])>{!! $option['label'] !!}</span>
                @if($isSelected)
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke="currentColor" fill="none" stroke-width="2" class="size-4 shrink-0 text-primary" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                    </svg>
                @endif
            </li>
        @endforeach
    </ul>
</div>
