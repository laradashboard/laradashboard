@props([
    'label' => 'Actions',
    'showLabel' => true,
    'align' => 'left', // left, right
    'buttonClass' => '',
    'position' => 'bottom', // bottom, top
])

<div
    x-data="{
        isOpen: false,
        openedWithKeyboard: false,
        updatePosition() {
            if (!this.isOpen) return;

            const button = this.$refs.button;
            const dropdown = this.$refs.dropdown;

            if (!button || !dropdown) return;

            const rect = button.getBoundingClientRect();

            // Position dropdown relative to viewport
            dropdown.style.position = 'fixed';
            dropdown.style.zIndex = '9999';

            if ('{{ $position }}' === 'top') {
                dropdown.style.top = (rect.top - dropdown.offsetHeight - 5) + 'px';
            } else {
                dropdown.style.top = (rect.bottom + 5) + 'px';
            }

            if ('{{ $align }}' === 'right') {
                dropdown.style.left = (rect.right - dropdown.offsetWidth) + 'px';
            } else {
                dropdown.style.left = rect.left + 'px';
            }
        }
    }"
    x-on:keydown.esc.window="isOpen = false; openedWithKeyboard = false"
    x-on:scroll.window="updatePosition()"
    x-on:resize.window="updatePosition()"
    role="menu"
    aria-label="{{ $label }}"
>
    <button
        x-ref="button"
        type="button"
        x-on:click="isOpen = !isOpen; $nextTick(() => updatePosition())"
        class="inline-flex items-center gap-2 whitespace-nowrap rounded-full bg-white p-2 text-sm font-medium text-gray-700 transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 {{ $buttonClass }}"
        aria-haspopup="true"
        x-on:keydown.space.prevent="openedWithKeyboard = true; $nextTick(() => updatePosition())"
        x-on:keydown.enter.prevent="openedWithKeyboard = true; $nextTick(() => updatePosition())"
        x-on:keydown.down.prevent="openedWithKeyboard = true; $nextTick(() => updatePosition())"
        x-bind:aria-expanded="isOpen || openedWithKeyboard"
    >
        <iconify-icon icon="lucide:more-horizontal" width="20"></iconify-icon>
        @if($showLabel)
            <span class="hidden sm:inline">{{ $label }}</span>
        @endif
    </button>

    <template x-teleport="body">
        <div
            x-cloak
            x-ref="dropdown"
            x-show="isOpen || openedWithKeyboard"
            x-transition
            x-trap="openedWithKeyboard"
            x-on:click.outside="isOpen = false; openedWithKeyboard = false"
            x-on:keydown.down.prevent="$focus.wrap().next()"
            x-on:keydown.up.prevent="$focus.wrap().previous()"
            class="w-fit min-w-48 flex-col overflow-hidden rounded-md border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-800"
            style="position: fixed; z-index: 9999;"
            role="menu"
        >
            {{ $slot }}
        </div>
    </template>
</div>
