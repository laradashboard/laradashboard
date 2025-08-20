@props([
    'columns' => [],
    'data' => [],
    'actions' => [],
    'bulkActions' => [],
    'filters' => [],
    'showCheckboxes' => false,
    'showSearch' => false,
    'showFilters' => false,
    'showBulkActions' => false,
    'showPagination' => false,
    'showActions' => false,
    'emptyMessage' => 'No records found',
    'searchPlaceholder' => 'Search...',
    'createRoute' => null,
    'createButtonText' => 'Create New',
    'showCreateButton' => false,
    'sortableColumns' => [],
    'searchableColumns' => [],
])

<div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6" x-data="{ selectedItems: [], selectAll: false, bulkActionModalOpen: false }">
    <div class="space-y-6">
        <div class="rounded-md border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            {{-- Header Actions --}}
            <div class="table-td sm:py-5 flex flex-col md:flex-row justify-between items-center gap-3">
                {{-- Search --}}
                @if($showSearch)
                    <x-page-generator.search 
                        :placeholder="$searchPlaceholder"
                    />
                @endif
                
                <div class="flex items-center gap-3">
                    {{-- Bulk Actions --}}
                    @if($showBulkActions && count($bulkActions) > 0)
                        <x-page-generator.bulk-actions 
                            :actions="$bulkActions"
                            x-show="selectedItems.length > 0"
                        />
                    @endif
                    
                    {{-- Filters --}}
                    @if($showFilters && count($filters) > 0)
                        <x-page-generator.filters 
                            :filters="$filters"
                        />
                    @endif
                    
                    {{-- Create Button --}}
                    @if($showCreateButton && $createRoute)
                        <a href="{{ $createRoute }}" class="btn-primary flex items-center gap-2">
                            <iconify-icon icon="feather:plus" height="16"></iconify-icon>
                            {{ $createButtonText }}
                        </a>
                    @endif
                </div>
            </div>
            
            {{-- Table --}}
            <div class="table-responsive">
                <table class="table">
                    <thead class="table-thead">
                        <tr class="table-tr">
                            @if($showCheckboxes)
                                <th width="3%" class="table-thead-th">
                                    <div class="flex items-center">
                                        <input
                                            type="checkbox"
                                            class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded focus:ring-primary dark:focus:ring-primary dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                                            x-model="selectAll"
                                            @click="
                                                selectAll = !selectAll;
                                                selectedItems = selectAll ?
                                                    [...document.querySelectorAll('.item-checkbox')].map(cb => cb.value) :
                                                    [];
                                            "
                                        >
                                    </div>
                                </th>
                            @endif
                            
                            @foreach($columns as $column)
                                <th class="table-thead-th" style="width: {{ $column['width'] ?? 'auto' }}">
                                    <div class="flex items-center">
                                        {{ $column['label'] ?? $column['name'] }}
                                        
                                        @if(in_array($column['name'], $sortableColumns))
                                            <x-page-generator.sort-icon 
                                                :field="$column['name']"
                                            />
                                        @endif
                                    </div>
                                </th>
                            @endforeach
                            
                            @if($showActions)
                                <th class="table-thead-th table-thead-th-last">{{ __('Actions') }}</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($data as $item)
                            <tr class="{{ !$loop->last ? 'table-tr' : '' }}">
                                @if($showCheckboxes)
                                    <td class="table-td table-td-checkbox">
                                        <input
                                            type="checkbox"
                                            class="item-checkbox form-checkbox h-4 w-4 text-primary border-gray-300 rounded focus:ring-primary dark:focus:ring-primary dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                                            value="{{ $item->id }}"
                                            x-model="selectedItems"
                                        >
                                    </td>
                                @endif
                                
                                @foreach($columns as $column)
                                    <td class="table-td">
                                        <x-page-generator.column-value 
                                            :column="$column" 
                                            :item="$item"
                                        />
                                    </td>
                                @endforeach
                                
                                @if($showActions)
                                    <td class="table-td flex justify-center">
                                        <x-page-generator.row-actions 
                                            :actions="$actions" 
                                            :item="$item"
                                        />
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($columns) + ($showCheckboxes ? 1 : 0) + ($showActions ? 1 : 0) }}" 
                                    class="text-center py-4">
                                    <p class="text-gray-500 dark:text-gray-300">{{ $emptyMessage }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                
                @if($showPagination && method_exists($data, 'links'))
                    <div class="my-4 px-4 sm:px-6">
                        {{ $data->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>