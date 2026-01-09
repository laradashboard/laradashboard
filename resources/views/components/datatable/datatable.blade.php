@props([
    'title' => '',
    'enableLivewire' => true,

    'enableSearchbar' => true,
    'searchbarPlaceholder' => __('Search...'),
    'customSeachForm' => null,

    'enableFilters' => true,
    'filters' => [],
    'customFilters' => null,
    'enableBulkActions' => true,
    'customBulkActions' => null,
    'direction' => 'desc',

    'enableNewResourceLink' => false,
    'newResourceLinkPermission' => '',
    'newResourceLinkIcon' => 'feather:plus',
    'newResourceLinkRouteName' => '',
    'newResourceLinkRouteUrl' => '',
    'newResourceLinkLabel' => __('Create New'),
    'customNewResourceLink' => null,

    'data' => [],
    'enableCheckbox' => true,
    'noResultsMessage' => __('No data found.'),
    'customNoResultsMessage' => null,
    'enablePagination' => true,
    'headers' => [],
    'sort' => '',
    'perPage' => 10,
    'perPageOptions' => [10, 20, 50, 100, __('All')],
])

@php
    $allIds = $data->getCollection()->pluck('id')->toArray();
@endphp

<div class="space-y-6"
     x-data="{
        selectedItems: Array.isArray($wire.selectedItems) ? $wire.selectedItems : [],
        selectAll: false,
        allIds: {{ json_encode($allIds) }},
        bulkDeleteModalOpen: false,
        toggleSelectAll() {
            if (this.selectAll) {
                // Add current page items to selection (preserve items from other pages)
                this.allIds.forEach(id => {
                    if (!this.selectedItems.includes(id)) {
                        this.selectedItems.push(id);
                    }
                });
            } else {
                // Remove only current page items from selection (preserve items from other pages)
                this.selectedItems = this.selectedItems.filter(id => !this.allIds.includes(id));
            }
            // Update all checkboxes on current page
            document.querySelectorAll('.item-checkbox').forEach(checkbox => {
                checkbox.checked = this.selectAll;
            });
            // Sync with Livewire
            if (@json($enableLivewire)) {
                $wire.set('selectedItems', this.selectedItems);
            }
        },
        updateSelectAll() {
            // Check if all current page items are selected
            this.selectAll = this.allIds.length > 0 && this.allIds.every(id => this.selectedItems.includes(id));
            // Sync with Livewire
            if (@json($enableLivewire)) {
                $wire.set('selectedItems', this.selectedItems);
            }
        },
        // Method to refresh allIds when Livewire updates
        refreshIds(newIds) {
            this.allIds = newIds;
            // Filter selectedItems to only include items that still exist
            this.selectedItems = this.selectedItems.filter(id => newIds.includes(id));
            this.updateSelectAll();
        },
        init() {
            // Set initial selectAll state based on loaded selectedItems
            this.selectAll = this.allIds.length > 0 && this.allIds.every(id => this.selectedItems.includes(id));

            // Update allIds when Livewire re-renders (e.g., pagination/perPage change)
            Livewire.hook('morph.updated', ({ el, component }) => {
                if (el === this.$root) {
                    // Get fresh IDs from the DOM after Livewire update
                    const checkboxes = this.$root.querySelectorAll('.item-checkbox');
                    // Handle both numeric and string IDs
                    this.allIds = Array.from(checkboxes).map(cb => {
                        const val = cb.value;
                        const num = parseInt(val);
                        return isNaN(num) ? val : num;
                    });
                    // Update selectAll state based on new page items
                    this.selectAll = this.allIds.length > 0 && this.allIds.every(id => this.selectedItems.includes(id));
                }
            });

            window.addEventListener('resetSelectedItems', () => {
                this.selectedItems = [];
                this.selectAll = false;

                // Uncheck all checkboxes.
                document.querySelectorAll('.item-checkbox').forEach(checkbox => {
                    checkbox.checked = false;
                });
            });
        }
     }"
>
    <div class="rounded-md border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="px-5 py-4 sm:px-6 sm:py-5 flex flex-col md:flex-row justify-between items-center gap-3">
            {!! Hook::applyFilters(DatatableHook::BEFORE_SEARCHBOX, '', $searchbarPlaceholder) !!}
            @if($enableLivewire)
                {{ method_exists($this, 'renderBeforeSearchbar') ? $this->renderBeforeSearchbar() : '' }}
            @endif

            @if($enableSearchbar)
                @if($customSeachForm)
                    {!! $customSeachForm !!}
                @else
                    <x-datatable.searchbar
                        :placeholder="$searchbarPlaceholder"
                        :enableLivewire="$enableLivewire"
                    />
                @endif
            @endif

            @if($enableLivewire)
                {{ method_exists($this, 'renderAfterSearchbar') ? $this->renderAfterSearchbar() : '' }}
            @endif
            {!! Hook::applyFilters(DatatableHook::AFTER_SEARCHBOX, '', $searchbarPlaceholder) !!}

            <div class="flex items-center gap-3 flex-wrap md:flex-nowrap md:w-auto">
                <div
                    class="flex items-center gap-2"
                    x-show="selectedItems.length > 0"
                >
                    @if($enableBulkActions)
                        @if($customBulkActions)
                            {!! $customBulkActions !!}
                        @else
                            <div class="relative flex items-center" x-data="{ open: false }">
                                <button @click="open = !open" class="btn-secondary flex items-center gap-2 text-sm whitespace-nowrap" type="button">
                                    <iconify-icon icon="lucide:more-vertical"></iconify-icon>
                                    <span>{{ __('Bulk Actions') }} (<span x-text="selectedItems.length"></span>)</span>
                                    <iconify-icon icon="lucide:chevron-down"></iconify-icon>
                                </button>
                                <div x-show="open" @click.outside="open = false" x-transition
                                        class="absolute right-0 top-10 mt-2 w-48 rounded-md shadow bg-white dark:bg-gray-700 z-10 p-2">
                                    <ul class="space-y-2">
                                        <li class="cursor-pointer flex items-center gap-1 text-sm text-red-600 dark:text-red-500 hover:bg-red-50 dark:hover:bg-red-500 dark:hover:text-red-50 px-2 py-1.5 rounded transition-colors duration-300"
                                            @click="open = false; bulkDeleteModalOpen = true">
                                            <iconify-icon icon="lucide:trash"></iconify-icon> {{ __('Delete Selected') }}
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <div
                                x-cloak
                                x-show="bulkDeleteModalOpen"
                                x-transition.opacity.duration.200ms
                                x-trap.inert.noscroll="bulkDeleteModalOpen"
                                x-on:keydown.esc.window="bulkDeleteModalOpen = false"
                                x-on:click.self="bulkDeleteModalOpen = false"
                                class="fixed inset-0 z-50 flex items-center justify-center bg-black/20 p-4 backdrop-blur-md"
                                role="dialog"
                                aria-modal="true"
                                aria-labelledby="bulk-delete-modal-title"
                            >
                                <div
                                    x-show="bulkDeleteModalOpen"
                                    x-transition:enter="transition ease-out duration-200 delay-100 motion-reduce:transition-opacity"
                                    x-transition:enter-start="opacity-0 scale-50"
                                    x-transition:enter-end="opacity-100 scale-100"
                                    class="flex max-w-md flex-col gap-4 overflow-hidden rounded-md border border-outline border-gray-100 dark:border-gray-800 bg-white text-on-surface dark:border-outline-dark dark:bg-gray-700 dark:text-gray-300"
                                >
                                    <div class="flex items-center justify-between border-b border-gray-100 px-4 py-2 dark:border-gray-800">
                                        <div class="flex items-center justify-center rounded-full bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400 p-1">
                                            <svg class="w-6 h-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 11V6m0 8h.01M19 10a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                                            </svg>
                                        </div>
                                        <h3 id="bulk-delete-modal-title" class="font-semibold tracking-wide text-gray-700 dark:text-white">
                                            {{ __('Delete Selected Items') }}
                                        </h3>
                                        <button
                                            x-on:click="bulkDeleteModalOpen = false"
                                            aria-label="close modal"
                                            class="text-gray-400 hover:bg-gray-200 hover:text-gray-700 rounded-md p-1 dark:hover:bg-gray-600 dark:hover:text-white"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="1.4" class="w-5 h-5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </div>
                                    <div class="px-4 text-center">
                                        <p class="text-gray-500 dark:text-gray-300">
                                            {{ __('Are you sure you want to delete the selected items?') }}
                                            {{ __('This action cannot be undone.') }}
                                        </p>
                                    </div>
                                    <div class="flex items-center justify-end gap-3 border-t border-gray-100 p-4 dark:border-gray-800">
                                        @if($this->getBulkDeleteAction()['url'])
                                            <form id="bulk-delete-form" action="{{ $this->getBulkDeleteAction()['url'] }}" method="POST">
                                                @method($this->getBulkDeleteAction()['method'])
                                                @csrf

                                                <template x-for="id in selectedItems" :key="id">
                                                    <input type="hidden" name="ids[]" :value="id">
                                                </template>

                                                <button
                                                    type="button"
                                                    x-on:click="bulkDeleteModalOpen = false"
                                                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:text-white dark:focus:ring-gray-700"
                                                >
                                                    {{ __('No, Cancel') }}
                                                </button>

                                                <button
                                                    type="submit"
                                                    class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-800 focus:outline-none focus:ring-2 focus:ring-red-300 dark:focus:ring-red-800"
                                                >
                                                    {{ __('Yes, Delete') }}
                                                </button>
                                            </form>
                                        @else
                                            <button
                                                type="button"
                                                x-on:click="bulkDeleteModalOpen = false"
                                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:text-white dark:focus:ring-gray-700"
                                            >
                                                {{ __('No, Cancel') }}
                                            </button>
                                            <button
                                                type="button"
                                                @click="bulkDeleteModalOpen = false"
                                                @if($enableLivewire)
                                                    wire:click="bulkDelete"
                                                    wire:loading.attr="disabled"
                                                @endif
                                                class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-800 focus:outline-none focus:ring-2 focus:ring-red-300 dark:focus:ring-red-800"
                                            >
                                                {{ __('Yes, Delete') }}
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endif
                </div>

                @if($enableFilters)
                    @if($customFilters)
                        {!! $customFilters !!}
                    @else
                        @if(isset($filters) && count($filters) > 0)
                            <x-datatable.responsive-filters
                                :filters="$filters"
                                :enableLivewire="$enableLivewire"
                                :hasActiveFilters="$enableLivewire && method_exists($this, 'hasActiveFilters') && $this->hasActiveFilters()"
                            />
                        @endif
                    @endif
                @endif

                @if($enableNewResourceLink)
                    @if($customNewResourceLink)
                        {!! $customNewResourceLink !!}
                    @elseif($newResourceLinkPermission && ($newResourceLinkRouteUrl || $newResourceLinkRouteName) && auth()->user()->can($newResourceLinkPermission))
                        <a href="{{ $newResourceLinkRouteUrl ?: route($newResourceLinkRouteName) }}" class="btn-primary flex items-center gap-2">
                            <iconify-icon icon="{{ $newResourceLinkIcon }}" height="16"></iconify-icon>
                            {{ $newResourceLinkLabel }}
                        </a>
                    @endif
                @endif
            </div>
        </div>

        <div class="table-responsive">
            <table id="dataTable" class="table">
                <thead class="table-thead">
                    <tr class="table-tr">
                        @if($enableCheckbox ?? true)
                            <th width="3%" class="table-thead-th" wire:ignore>
                                <div class="flex items-center">
                                    <input
                                        type="checkbox"
                                        class="form-checkbox"
                                        x-model="selectAll"
                                        @change="toggleSelectAll()"
                                    >
                                </div>
                            </th>
                        @endif

                        @foreach($headers ?? [] as $header)
                        <th
                            @isset($header['width']) width="{{ $header['width'] }}" @endisset
                            class="table-thead-th {{ count($headers) - 1 === $loop->index ? 'table-thead-th-last' : '' }} {{ isset($header['align']) ? 'text-' . $header['align'] : '' }}"
                        >
                            <div class="flex w-full items-center {{ isset($header['align']) ? 'justify-' . ($header['align'] === 'right' ? 'end' : 'start') : '' }}">
                                {{ __($header['title']) }}

                                @if($header['sortable'] ?? false)
                                <button
                                    @if($enableLivewire)
                                        wire:click="sortBy('{{ $header['sortBy'] ?? strtolower(str_replace(' ', '_', $header['title'])) }}')"
                                    @else
                                        @php
                                            $sortKey = $header['sortBy'] ?? strtolower(str_replace(' ', '_', $header['title']));
                                            $nextDirection = ($sort === $sortKey && $direction === 'asc') ? 'desc' : 'asc';
                                        @endphp
                                        onclick="window.location='{{ request()->fullUrlWithQuery(['sort' => $sortKey, 'direction' => $nextDirection]) }}'"
                                    @endif
                                    class="ml-1 focus:outline-none"
                                >
                                    @if(isset($header['sortBy']) && $sort === $header['sortBy'] && $direction === 'asc')
                                        <iconify-icon icon="lucide:sort-asc" class="text-primary"></iconify-icon>
                                    @elseif(isset($header['sortBy']) && $sort === $header['sortBy'] && $direction === 'desc')
                                        <iconify-icon icon="lucide:sort-desc" class="text-primary"></iconify-icon>
                                    @else
                                        <iconify-icon icon="lucide:arrow-up-down" class="text-gray-400"></iconify-icon>
                                    @endif
                                </button>
                                @endif
                            </div>
                        </th>
                        @endforeach
                    </tr>
                </thead>

                <tbody>
                    @forelse ($data as $item)
                        @php
                            $afterRowContent = ($enableLivewire && method_exists($this, 'renderAfterRow')) ? $this->renderAfterRow($item) : null;
                            $hasAfterRow = $afterRowContent !== null;
                            $showRowBorder = !$hasAfterRow && $loop->index + 1 != count($data);
                        @endphp
                        <tr class="{{ $showRowBorder ? 'table-tr' : '' }}">
                            @if($enableCheckbox ?? true && can('delete', $item))
                                @php
                                    $itemId = $item->id;
                                    $itemIdJson = json_encode($itemId);
                                @endphp
                                <td class="table-td table-td-checkbox" wire:ignore>
                                    <input
                                        type="checkbox"
                                        class="item-checkbox form-checkbox"
                                        value="{{ $itemId }}"
                                        :checked="selectedItems.includes({{ $itemIdJson }})"
                                        @change="
                                            const itemId = {{ $itemIdJson }};
                                            if ($event.target.checked) {
                                                if (!selectedItems.includes(itemId)) {
                                                    selectedItems.push(itemId);
                                                }
                                            } else {
                                                selectedItems = selectedItems.filter(id => id !== itemId);
                                            }
                                            updateSelectAll();
                                        "
                                    />
                                </td>
                            @endif

                            @foreach($headers ?? [] as $header)
                                <td class="table-td {{ isset($header['align']) ? 'text-' . $header['align'] : '' }}">
                                    @php
                                        $pascalCaseId = collect(explode('_', $header['id']))->map(fn($part) => ucfirst($part))->implode('');
                                        $content = isset($data[$loop->index][$header['id']]) ? $data[$loop->index][$header['id']] : null;
                                        if ($enableLivewire){
                                            // Convert snake_case to PascalCase for method name discovery
                                            $autoDiscoverableMethodName = 'render' . $pascalCaseId . 'Column';

                                            // Custom Blade include/component.
                                            if (isset($header['renderContent']) && is_string($header['renderContent'])) {
                                                $content = $this->{$header['renderContent']}($item, $header);
                                            } elseif (isset($header['renderRawContent'])) {
                                                $content = $header['renderRawContent'];
                                            } elseif (method_exists($this, $autoDiscoverableMethodName)) { // Auto-discovered method - `render[Id]Cell()`
                                                $content = $this->{$autoDiscoverableMethodName}($item, $header);
                                            } elseif (isset($item->{$header['id']})) { // model property
                                                $content = $item->{$header['id']} ?? '';
                                            }
                                        }
                                    @endphp
                                    {!! $content !!}
                                </td>
                            @endforeach
                        </tr>

                        {{-- Hook for rendering additional content after row (e.g., update notices) --}}
                        @if($hasAfterRow)
                            <tr class="after-row-content {{ $loop->index + 1 != count($data) ? 'table-tr' : '' }}">
                                <td colspan="{{ count($headers ?? []) + ($enableCheckbox ?? true ? 1 : 0) }}" class="p-0 border-0">
                                    {{ $afterRowContent }}
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="{{ count($headers ?? []) + ($enableCheckbox ?? true ? 1 : 0) }}" class="text-center py-4">
                                <p class="text-gray-500 dark:text-gray-300">
                                    @if($customNoResultsMessage ?? false)
                                        {!! $customNoResultsMessage !!}
                                    @else
                                        {!! $noResultsMessage ?? __('No data found.') !!}
                                    @endif
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @if($enablePagination ?? true)
                <div class="my-4 px-4 sm:px-6 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-2">
                        <label for="perPage" class="text-sm text-gray-600 dark:text-gray-300">{{ __('Per page') }}</label>
                        <select
                            id="perPage"
                            wire:model.live="perPage"
                            class="form-control w-20"
                        >
                            @foreach($perPageOptions as $option)
                                <option value="{{ $option == 'All' ? 999999 : $option }}">
                                    {{ $option }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="pagination-links">
                        {{ $data->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>