@props([
    'name' => '',
    'label' => '',
    'placeholder' => 'Select an option...',
    'searchPlaceholder' => 'Search...',
    'options' => [],
    'selected' => '',
    'required' => false,
    'disabled' => false,
    'position' => 'bottom', // 'top' or 'bottom'
])

<div class="space-y-1" x-data="searchableSelect({
    options: @js($options),
    selected: '{{ $selected }}',
    name: '{{ $name }}',
    placeholder: '{{ $placeholder }}',
    searchPlaceholder: '{{ $searchPlaceholder }}'
})">
    @if($label)
        <label for="{{ $name }}" class="form-label">
            {{ $label }}
            @if($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif

    <div class="relative">
        <!-- Hidden input to store the selected value -->
        <input type="hidden" name="{{ $name }}" x-model="selectedValue" />

        <!-- Main select button -->
        <button
            type="button"
            class="form-control flex items-center justify-between w-full text-left {{ $disabled ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer' }}"
            @click="toggleDropdown"
            :disabled="{{ $disabled ? 'true' : 'false' }}"
            :class="{ 'ring-2 ring-brand-500': isOpen }"
        >
            <span x-text="selectedText" class="truncate"></span>
            <svg class="w-5 h-5 text-gray-400 transition-transform duration-200"
                 :class="{ 'rotate-180': isOpen }"
                 fill="none"
                 stroke="currentColor"
                 viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </button>

        <!-- Dropdown -->
        <div
            x-show="isOpen"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 {{ $position === 'top' ? '-translate-y-1' : 'translate-y-1' }}"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 {{ $position === 'top' ? '-translate-y-1' : 'translate-y-1' }}"
            @click.away="closeDropdown"
            class="absolute z-50 w-full {{ $position === 'top' ? 'bottom-full mb-1' : 'top-full mt-1' }} bg-white border border-gray-300 rounded-md shadow-lg dark:bg-gray-800 dark:border-gray-600 max-h-60 overflow-hidden"
        >
            <!-- Search input -->
            <div class="p-2 border-b border-gray-200 dark:border-gray-600">
                <input
                    type="text"
                    x-model="searchQuery"
                    @input="filterOptions"
                    :placeholder="searchPlaceholder"
                    class="form-control w-full"
                >
            </div>

            <!-- Options list -->
            <div class="max-h-48 overflow-y-auto">
                <template x-for="option in filteredOptions" :key="option.key">
                    <button
                        type="button"
                        class="w-full px-3 py-2 text-sm text-left hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-700 transition-colors duration-150"
                        :class="{ 'bg-brand-50 text-brand-700 dark:bg-brand-900 dark:text-brand-200': selectedValue === option.key }"
                        @click="selectOption(option)"
                        x-text="option.value"
                    ></button>
                </template>

                <!-- No results message -->
                <div x-show="filteredOptions.length === 0" class="px-3 py-2 text-sm text-gray-500 dark:text-gray-400">
                    No results found
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function searchableSelect(config) {
    return {
        isOpen: false,
        searchQuery: '',
        selectedValue: config.selected || '',
        selectedText: '',
        options: [],
        filteredOptions: [],
        searchPlaceholder: config.searchPlaceholder || 'Search...',

        init() {
            // Convert options object to array format
            this.options = Object.entries(config.options).map(([key, value]) => ({
                key: key,
                value: value
            }));

            this.filteredOptions = this.options;
            this.updateSelectedText();
        },

        toggleDropdown() {
            this.isOpen = !this.isOpen;
            if (this.isOpen) {
                this.$nextTick(() => {
                    this.$el.querySelector('input[type="text"]')?.focus();
                });
            }
        },

        closeDropdown() {
            this.isOpen = false;
            this.searchQuery = '';
            this.filteredOptions = this.options;
        },

        selectOption(option) {
            this.selectedValue = option.key;
            this.updateSelectedText();
            this.closeDropdown();
        },

        updateSelectedText() {
            if (this.selectedValue) {
                const selectedOption = this.options.find(opt => opt.key === this.selectedValue);
                this.selectedText = selectedOption ? selectedOption.value : config.placeholder;
            } else {
                this.selectedText = config.placeholder;
            }
        },

        filterOptions() {
            if (!this.searchQuery.trim()) {
                this.filteredOptions = this.options;
                return;
            }

            const query = this.searchQuery.toLowerCase();
            this.filteredOptions = this.options.filter(option =>
                option.value.toLowerCase().includes(query) ||
                option.key.toLowerCase().includes(query)
            );
        }
    }
}
</script>
