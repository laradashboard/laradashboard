@props([
    'name' => 'icon',
    'label' => 'Icon',
    'labelClass' => '',
    'placeholder' => 'Search icons...',
    'selected' => '',
    'required' => false,
    'class' => '',
    'disabled' => false,
    'helpText' => '',
])

@php
    // Get icons from the IconService
    $iconService = app(\App\Services\IconService::class);
    $icons = $iconService->getBootstrapIcons();
    
    // Normalize icons to array of objects with 'value', 'label', and 'icon'.
    $normalizedIcons = [];
    foreach ($icons as $icon) {
        $normalizedIcons[] = [
            'value' => $icon,
            'label' => str_replace('bi-', '', $icon), // Remove prefix for display
            'icon' => $icon
        ];
    }
    
    $selectedValue = old($name, $selected);
@endphp

<div x-data="iconComboboxData({
    allIcons: {{ json_encode($normalizedIcons) }} ?? [],
    icons: {{ json_encode($normalizedIcons) }} ?? [],
    selectedIcon: {{ json_encode($selectedValue) }},
    placeholder: '{{ __($placeholder) }}',
    name: '{{ $name }}'
})"
    class="w-full flex flex-col {{ $class }}"
    x-on:keydown.esc.window="isOpen = false, openedWithKeyboard = false"
    {{ $attributes }}>

    @if($label)
        <label for="{{ $name }}" class="form-label {{ $labelClass }}">
            {{ __($label) }}
            @if($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif

    <div class="relative">
        <!-- Trigger button -->
        <button type="button"
            role="combobox"
            class="form-control-combobox"
            x-on:click="isOpen = !isOpen; if (isOpen || openedWithKeyboard) { $nextTick(() => $refs.searchField && $refs.searchField.focus()); }"
            x-on:keydown.down.prevent="openedWithKeyboard = true; if (isOpen || openedWithKeyboard) { $nextTick(() => $refs.searchField && $refs.searchField.focus()); }"
            x-on:keydown.enter.prevent="openedWithKeyboard = true; if (isOpen || openedWithKeyboard) { $nextTick(() => $refs.searchField && $refs.searchField.focus()); }"
            x-on:keydown.space.prevent="openedWithKeyboard = true; if (isOpen || openedWithKeyboard) { $nextTick(() => $refs.searchField && $refs.searchField.focus()); }"
            x-bind:aria-expanded="isOpen || openedWithKeyboard"
            @if($disabled) disabled @endif>
            <span class="flex items-center gap-2 text-sm font-normal text-left truncate">
                <template x-if="selectedIcon">
                    <iconify-icon x-bind:icon="selectedIcon" class="text-xl"></iconify-icon>
                </template>
                <span x-text="getLabelText()"></span>
            </span>
            <iconify-icon
                icon="mdi:chevron-down"
                class="text-2xl"
                :class="(isOpen || openedWithKeyboard) ? 'text-gray-400 dark:text-gray-300 rotate-180 transition-transform duration-200' : 'text-gray-400 dark:text-gray-300 transition-transform duration-200'"
            ></iconify-icon>
        </button>

        <!-- Hidden select for form submission -->
        <select
            name="{{ $name }}"
            id="{{ $name }}"
            x-ref="hiddenTextField"
            x-bind:value="selectedIcon"
            @if($required) required @endif
            aria-hidden="true"
            tabindex="-1"
            class="sr-only"
            style="position: absolute; pointer-events: none;">
            <option value="">{{ __('Select an icon') }}</option>
            @foreach($normalizedIcons as $icon)
                <option value="{{ $icon['value'] }}">{{ $icon['label'] }}</option>
            @endforeach
        </select>

        <!-- Dropdown panel -->
        <div
            x-cloak
            x-show="isOpen || openedWithKeyboard"
            class="absolute z-50 left-0 top-full mt-1 w-full overflow-hidden rounded-md border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-900"
            @click.outside="isOpen = false; openedWithKeyboard = false;"
            x-on:keydown.down.prevent="$focus.wrap().next()"
            x-on:keydown.up.prevent="$focus.wrap().previous()"
            x-transition
            x-trap="openedWithKeyboard"
        >

            <!-- Search input -->
            <div class="border-b border-gray-200 dark:border-gray-700 p-2">
                <input type="text"
                    autofocus
                    class="form-control"
                    placeholder="{{ __('Search icons...') }}"
                    x-model="searchQuery"
                    x-on:input="filterIcons(searchQuery)"
                    x-ref="searchField" />
            </div>

            <!-- Icons list -->
            <ul class="max-h-60 overflow-y-auto py-1">
                <template x-for="(item, index) in icons" x-bind:key="item.value">
                    <li class="combobox-option px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-white/90 dark:hover:bg-gray-800 cursor-pointer flex items-center justify-between"
                        role="option"
                        x-on:click="selectIcon(item)"
                        x-on:keydown.enter="selectIcon(item)"
                        x-bind:id="'icon_option_' + index"
                        tabindex="0">
                        <div class="flex items-center gap-3">
                            <iconify-icon x-bind:icon="item.icon" class="text-xl text-gray-600 dark:text-gray-300"></iconify-icon>
                            <span x-bind:class="selectedIcon == item.value ? 'font-medium' : ''" x-text="item.label"></span>
                        </div>
                        <svg x-cloak x-show="selectedIcon == item.value" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke="currentColor" fill="none" stroke-width="2" class="size-4 text-primary">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                        </svg>
                    </li>
                </template>

                <li x-show="icons.length === 0" class="px-4 py-2 text-sm text-gray-500 dark:text-gray-300">
                    {{ __('No icons found') }}
                </li>
            </ul>
        </div>
    </div>

    @if($helpText)
        <p class="mt-1 text-xs text-gray-500">{{ __($helpText) }}</p>
    @endif
</div>

@once
@push('scripts')
<script>
function iconComboboxData({
    allIcons = [],
    icons = [],
    selectedIcon = null,
    placeholder = 'Search icons...',
    name = 'icon'
}) {
    return {
        allIcons,
        icons,
        isOpen: false,
        openedWithKeyboard: false,
        selectedIcon,
        searchQuery: '',
        placeholder,
        name,
        
        getLabelText() {
            if (!this.selectedIcon) {
                return this.placeholder;
            }
            
            const icon = this.allIcons.find(i => i.value === this.selectedIcon);
            return icon ? icon.label : this.selectedIcon;
        },
        
        filterIcons(query) {
            if (!query || query.trim() === '') {
                this.icons = this.allIcons;
                return;
            }
            
            const searchTerm = query.toLowerCase().trim();
            this.icons = this.allIcons.filter(icon => {
                return icon.label.toLowerCase().includes(searchTerm) || 
                       icon.value.toLowerCase().includes(searchTerm);
            });
        },
        
        selectIcon(icon) {
            this.selectedIcon = icon.value;
            this.isOpen = false;
            this.openedWithKeyboard = false;
            this.searchQuery = '';
            this.icons = this.allIcons;
        },
        
        init() {
            // Initialize with all icons
            this.icons = this.allIcons;
        }
    }
}
</script>
@endpush
@endonce
