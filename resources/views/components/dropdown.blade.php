@props([
    'options' => [], // Array of ['value' => '', 'label' => '', 'href' => null]
    'label' => null,
    'buttonLabel' => 'Select...',
    'selected' => null,
    'name' => null,
    'class' => '',
])

<div x-data="{
        isOpen: false,
        openedWithKeyboard: false,
        selected: @js($selected ?? ($options[0]['value'] ?? null)),
        get selectedLabel() {
            const found = this.selected ? this.options.find(o => o.value === this.selected) : null;
            return found ? found.label : this.buttonLabel;
        },
        options: @js($options),
        buttonLabel: @js($buttonLabel),
    }"
    class="relative w-fit {{ $class }}"
    x-on:keydown.esc.window="isOpen = false; openedWithKeyboard = false"
>
    @if($label)
        <label class="block mb-1 font-medium text-gray-700 dark:text-gray-300">{{ $label }}</label>
    @endif

    <!-- Toggle Button -->
    <button
        type="button"
        @click="isOpen = !isOpen"
        class="inline-flex items-center gap-2 whitespace-nowrap rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 px-4 py-2 text-sm font-medium tracking-wide transition hover:opacity-75 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-500 dark:focus-visible:outline-primary-400"
        aria-haspopup="true"
        x-on:keydown.space.prevent="openedWithKeyboard = true"
        x-on:keydown.enter.prevent="openedWithKeyboard = true"
        x-on:keydown.down.prevent="openedWithKeyboard = true"
        x-bind:aria-expanded="isOpen || openedWithKeyboard"
    >
        <span x-text="selectedLabel"></span>
        <iconify-icon icon="mdi:chevron-down" :class="(isOpen || openedWithKeyboard) ? 'text-2xl rotate-180 transition-transform' : 'text-2xl transition-transform'" aria-hidden="true"></iconify-icon>
    </button>

    <!-- Dropdown Menu -->
    <div x-cloak x-show="isOpen || openedWithKeyboard" x-transition x-trap="openedWithKeyboard" x-on:click.outside="isOpen = false; openedWithKeyboard = false" x-on:keydown.down.prevent="$focus.wrap().next()" x-on:keydown.up.prevent="$focus.wrap().previous()" class="absolute top-18 left-0 flex w-fit min-w-48 flex-col overflow-hidden rounded-md border border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-lg z-20" role="menu">
        @if($slot->isEmpty())
            <template x-for="option in options" :key="option.value">
                <button type="button"
                    class="px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 w-full text-left focus-visible:bg-gray-100 dark:focus-visible:bg-gray-800 focus-visible:outline-none"
                    role="menuitem"
                    :class="selected === option.value ? 'font-bold bg-gray-100 dark:bg-gray-800' : ''"
                    x-on:click="selected = option.value; isOpen = false; openedWithKeyboard = false"
                >
                    <span x-text="option.label"></span>
                </button>
            </template>
        @else
            {{ $slot }}
        @endif
    </div>
    <input type="hidden" name="{{ $name }}" x-model="selected">
</div>
