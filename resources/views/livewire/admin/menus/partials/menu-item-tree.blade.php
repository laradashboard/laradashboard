@foreach($items ?? [] as $item)
    <div
        class="menu-item-wrapper"
        data-id="{{ $item->id }}"
        data-parent-id="{{ $item->parent_id ?? '' }}"
        wire:key="menu-item-{{ $item->id }}"
    >
        <div class="menu-item bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-3 {{ $depth > 0 ? 'ml-6' : '' }}">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    {{-- Drag Handle --}}
                    <span class="drag-handle cursor-move text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300">
                        <iconify-icon icon="lucide:grip-vertical"></iconify-icon>
                    </span>

                    {{-- Item Icon --}}
                    @if($item->icon)
                        <iconify-icon icon="{{ $item->icon }}" class="text-gray-500 dark:text-gray-400"></iconify-icon>
                    @endif

                    {{-- Item Label --}}
                    <div>
                        <span class="font-medium text-gray-900 dark:text-white">{{ $item->label }}</span>
                        <span class="ml-2 text-xs text-gray-500 dark:text-gray-400">
                            ({{ \App\Models\MenuItem::getAvailableTypes()[$item->type] ?? $item->type }})
                        </span>
                        @if($item->target)
                            <span class="ml-1 text-xs text-gray-400 dark:text-gray-500">
                                → {{ Str::limit($item->target, 30) }}
                            </span>
                        @endif
                        @if($item->target_blank)
                            <iconify-icon icon="lucide:external-link" class="ml-1 text-xs text-gray-400"></iconify-icon>
                        @endif
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex items-center gap-1">
                    {{-- Add Sub-item --}}
                    <button
                        type="button"
                        wire:click="showAddItemForm({{ $item->id }})"
                        class="p-1.5 text-gray-500 hover:text-primary-600 dark:text-gray-400 dark:hover:text-primary-400 rounded"
                        title="{{ __('Add sub-item') }}"
                    >
                        <iconify-icon icon="lucide:plus"></iconify-icon>
                    </button>

                    {{-- Edit --}}
                    <button
                        type="button"
                        wire:click="editItem({{ $item->id }})"
                        class="p-1.5 text-gray-500 hover:text-blue-600 dark:text-gray-400 dark:hover:text-blue-400 rounded"
                        title="{{ __('Edit') }}"
                    >
                        <iconify-icon icon="lucide:edit-2"></iconify-icon>
                    </button>

                    {{-- Delete --}}
                    <button
                        type="button"
                        wire:click="deleteItem({{ $item->id }})"
                        wire:confirm="{{ __('Are you sure you want to delete this menu item and all its children?') }}"
                        class="p-1.5 text-gray-500 hover:text-red-600 dark:text-gray-400 dark:hover:text-red-400 rounded"
                        title="{{ __('Delete') }}"
                    >
                        <iconify-icon icon="lucide:trash-2"></iconify-icon>
                    </button>
                </div>
            </div>
        </div>

        {{-- Children (recursive) --}}
        @if($item->hasChildren())
            <div class="children-container mt-2 space-y-2 sortable-list" data-parent-id="{{ $item->id }}">
                @include('livewire.admin.menus.partials.menu-item-tree', ['items' => $item->getChildren(), 'depth' => $depth + 1])
            </div>
        @else
            {{-- Empty drop zone for items without children --}}
            <div class="children-container mt-2 space-y-2 sortable-list hidden" data-parent-id="{{ $item->id }}"></div>
        @endif
    </div>
@endforeach
