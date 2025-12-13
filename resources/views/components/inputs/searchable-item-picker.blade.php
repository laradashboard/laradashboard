@props([
    'name' => '',
    'label' => '',
    'placeholder' => 'Search...',
    'items' => [],
    'itemKey' => 'id',
    'itemLabel' => 'name',
    'itemSubLabel' => null,
    'itemImage' => null,
    'itemPrice' => null,
    'pricePrefix' => '$',
    'emptyText' => 'No items found',
    'emptyIcon' => 'lucide:search',
    'searchKeys' => ['name'],
    'class' => '',
    'inputClass' => '',
])

@php
    // Convert collection to array if needed
    if ($items instanceof \Illuminate\Support\Collection) {
        $items = $items->toArray();
    }
@endphp

<div
    x-data="searchableItemPicker({
        items: @js($items),
        itemKey: '{{ $itemKey }}',
        itemLabel: '{{ $itemLabel }}',
        itemSubLabel: {{ $itemSubLabel ? "'$itemSubLabel'" : 'null' }},
        itemImage: {{ $itemImage ? "'$itemImage'" : 'null' }},
        itemPrice: {{ $itemPrice ? "'$itemPrice'" : 'null' }},
        searchKeys: @js($searchKeys),
        emptyText: '{{ __($emptyText) }}'
    })"
    class="relative {{ $class }}"
    {{ $attributes->whereStartsWith('x-on:') }}
>
    @if($label)
        <label class="form-label">{{ __($label) }}</label>
    @endif

    <div class="relative">
        <iconify-icon icon="lucide:search" class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></iconify-icon>
        <input
            type="text"
            x-model="search"
            @focus="open = true"
            @input="open = true"
            placeholder="{{ __($placeholder) }}"
            class="form-control {{ $inputClass }}"
            style="padding-left: 2.8rem;"
        >
    </div>

    <!-- Dropdown -->
    <div
        x-show="open && search.length > 0"
        @click.outside="open = false"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-1"
        class="absolute z-20 w-full mt-1 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 max-h-64 overflow-y-auto"
    >
        <template x-for="item in filteredItems" :key="item[itemKey]">
            <button
                type="button"
                @click="selectItem(item)"
                class="w-full flex items-center gap-3 px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700 text-left border-b border-gray-100 dark:border-gray-700 last:border-0 transition-colors"
            >
                <!-- Item Image (optional) -->
                <template x-if="itemImage">
                    <div class="w-10 h-10 rounded bg-gray-100 dark:bg-gray-700 flex items-center justify-center flex-shrink-0 overflow-hidden">
                        <template x-if="item[itemImage]">
                            <img :src="item[itemImage]" :alt="item[itemLabel]" class="w-full h-full object-cover rounded">
                        </template>
                        <template x-if="!item[itemImage]">
                            <iconify-icon icon="lucide:image" class="text-gray-400"></iconify-icon>
                        </template>
                    </div>
                </template>

                <!-- Item Content -->
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate" x-text="item[itemLabel]"></p>
                    <template x-if="itemSubLabel && item[itemSubLabel]">
                        <p class="text-xs text-gray-500 dark:text-gray-400" x-text="item[itemSubLabel]"></p>
                    </template>
                </div>

                <!-- Item Price (optional) -->
                <template x-if="itemPrice && item[itemPrice] !== undefined">
                    <span class="text-sm font-medium text-primary" x-text="'{{ $pricePrefix }}' + Number(item[itemPrice] || 0).toFixed(2)"></span>
                </template>
            </button>
        </template>

        <!-- No Results -->
        <div
            x-show="filteredItems.length === 0"
            class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 text-center"
        >
            <iconify-icon icon="{{ $emptyIcon }}" class="text-2xl text-gray-300 dark:text-gray-600 mb-1 block"></iconify-icon>
            <span x-text="emptyText"></span>
        </div>
    </div>

    {{ $slot ?? '' }}
</div>

@pushOnce('scripts')
<script>
function searchableItemPicker(config) {
    return {
        search: '',
        open: false,
        items: config.items || [],
        itemKey: config.itemKey,
        itemLabel: config.itemLabel,
        itemSubLabel: config.itemSubLabel,
        itemImage: config.itemImage,
        itemPrice: config.itemPrice,
        searchKeys: config.searchKeys || ['name'],
        emptyText: config.emptyText,

        get filteredItems() {
            if (!this.search.trim()) return [];

            const query = this.search.toLowerCase();
            return this.items.filter(item => {
                return this.searchKeys.some(key => {
                    const value = item[key];
                    return value && String(value).toLowerCase().includes(query);
                });
            });
        },

        selectItem(item) {
            this.search = '';
            this.open = false;

            // Dispatch custom event on window so parent Alpine components can catch it
            window.dispatchEvent(new CustomEvent('searchable-item-selected', {
                detail: { item },
                bubbles: true
            }));

            // Also dispatch on element for local listeners
            this.$el.dispatchEvent(new CustomEvent('item-selected', {
                detail: { item },
                bubbles: true
            }));
        },

        setItems(items) {
            this.items = items;
        }
    }
}
</script>
@endPushOnce
