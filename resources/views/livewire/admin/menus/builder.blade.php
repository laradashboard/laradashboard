<div>
    <x-card>
        <x-slot name="header">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-primary/10 text-primary">
                    <iconify-icon icon="lucide:list" class="text-xl"></iconify-icon>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Menu Items') }}</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 font-normal">{{ __('Drag items to reorder or nest them') }}</p>
                </div>
            </div>
        </x-slot>

        <x-slot name="headerRight">
            <button
                type="button"
                wire:click="showAddItemForm"
                class="btn btn-sm btn-primary"
            >
                <iconify-icon icon="lucide:plus" class="mr-1"></iconify-icon>
                {{ __('Add Item') }}
            </button>
        </x-slot>

        {{-- Flash Messages --}}
        @if (session()->has('success'))
            <div class="mb-4 p-3 bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200 rounded-lg text-sm">
                {{ session('success') }}
            </div>
        @endif

        {{-- Add/Edit Form --}}
        @if($showAddForm || $showEditForm)
            <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="flex items-center gap-2 mb-4">
                    <iconify-icon
                        icon="{{ $showEditForm ? 'lucide:edit-2' : 'lucide:plus-circle' }}"
                        class="text-primary"
                    ></iconify-icon>
                    <h4 class="text-sm font-medium text-gray-900 dark:text-white">
                        {{ $showEditForm ? __('Edit Menu Item') : __('Add Menu Item') }}
                        @if($addToParentId)
                            <span class="text-gray-500 font-normal">({{ __('as sub-item') }})</span>
                        @endif
                    </h4>
                </div>

                <form wire:submit="saveItem" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-inputs.input
                            name="label"
                            id="label"
                            :label="__('Label')"
                            :placeholder="__('Menu item text')"
                            wire:model="label"
                            required
                        />

                        <x-inputs.select
                            name="type"
                            id="type"
                            :label="__('Type')"
                            :options="$itemTypes"
                            wire:model.live="type"
                            required
                        />
                    </div>

                    <div>
                        @if($type === 'custom')
                            <x-inputs.input
                                name="target"
                                id="target"
                                :label="__('URL')"
                                :placeholder="__('https://example.com or /page-slug')"
                                wire:model="target"
                            />
                        @else
                            <x-inputs.select
                                name="target"
                                id="target"
                                :label="__('Select Target')"
                                :options="$targetOptions"
                                :placeholder="__('Select...')"
                                wire:model="target"
                            />
                        @endif
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-inputs.input
                            name="icon"
                            id="icon"
                            :label="__('Icon')"
                            :placeholder="__('e.g., lucide:home')"
                            :hint="__('Iconify icon name')"
                            wire:model="icon"
                        />

                        <x-inputs.input
                            name="cssClasses"
                            id="cssClasses"
                            :label="__('CSS Classes')"
                            :placeholder="__('Optional custom classes')"
                            wire:model="cssClasses"
                        />
                    </div>

                    <div>
                        <x-inputs.toggle
                            name="targetBlank"
                            :label="__('Open in new tab')"
                            wire:model="targetBlank"
                        />
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <button
                            type="button"
                            wire:click="cancelForm"
                            class="btn btn-sm btn-default"
                        >
                            {{ __('Cancel') }}
                        </button>
                        <button type="submit" class="btn btn-sm btn-primary">
                            <iconify-icon icon="lucide:save" class="mr-1"></iconify-icon>
                            {{ $showEditForm ? __('Update') : __('Add') }}
                        </button>
                    </div>
                </form>
            </div>
        @endif

        {{-- Menu Items Tree --}}
        @if($nestedItems->isEmpty())
            <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                <iconify-icon icon="lucide:list" class="text-4xl mb-2"></iconify-icon>
                <p>{{ __('No menu items yet. Click "Add Item" to get started.') }}</p>
            </div>
        @else
            <div
                id="menu-items-root"
                class="space-y-2 sortable-list"
                data-parent-id=""
            >
                @include('livewire.admin.menus.partials.menu-item-tree', ['items' => $nestedItems, 'depth' => 0])
            </div>
        @endif
    </x-card>

    @assets
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
    <style>
        .sortable-ghost { opacity: 0.4; }
        .sortable-chosen { box-shadow: 0 0 0 2px var(--color-primary, #3b82f6); border-radius: 0.5rem; }
        .sortable-drag { opacity: 1; }
        .menu-item-wrapper.dragging { opacity: 0.5; }
    </style>
    @endassets

    @script
    <script>
        let sortableInstances = [];

        function initMenuSortable() {
            // Destroy existing instances
            sortableInstances.forEach(instance => {
                if (instance && typeof instance.destroy === 'function') {
                    instance.destroy();
                }
            });
            sortableInstances = [];

            // Get all sortable containers
            const containers = document.querySelectorAll('.sortable-list');

            containers.forEach(container => {
                const sortable = new Sortable(container, {
                    group: 'menu-items',
                    animation: 150,
                    handle: '.drag-handle',
                    ghostClass: 'sortable-ghost',
                    chosenClass: 'sortable-chosen',
                    dragClass: 'sortable-drag',
                    fallbackOnBody: true,
                    swapThreshold: 0.65,

                    onStart: function(evt) {
                        // Show all hidden drop zones
                        document.querySelectorAll('.children-container.hidden').forEach(el => {
                            el.classList.remove('hidden');
                            el.style.minHeight = '40px';
                            el.style.border = '2px dashed #d1d5db';
                            el.style.borderRadius = '0.5rem';
                            el.style.marginLeft = '1.5rem';
                        });
                    },

                    onEnd: function(evt) {
                        // Hide empty drop zones again
                        document.querySelectorAll('.children-container').forEach(el => {
                            if (el.children.length === 0) {
                                el.classList.add('hidden');
                                el.style.minHeight = '';
                                el.style.border = '';
                                el.style.borderRadius = '';
                            }
                        });

                        // Collect new order
                        const items = collectItemsOrder();

                        // Send to Livewire
                        if (items.length > 0) {
                            $wire.reorderItems(items);
                        }
                    }
                });

                sortableInstances.push(sortable);
            });
        }

        function collectItemsOrder() {
            const items = [];
            let position = 0;

            function processContainer(container, parentId) {
                const children = container.querySelectorAll(':scope > .menu-item-wrapper');
                children.forEach(function(item) {
                    const itemId = item.dataset.id;
                    items.push({
                        id: parseInt(itemId),
                        parent_id: parentId ? parseInt(parentId) : null,
                        position: position++
                    });

                    // Process nested children
                    const childContainer = item.querySelector(':scope > .children-container');
                    if (childContainer) {
                        processContainer(childContainer, itemId);
                    }
                });
            }

            const rootContainer = document.getElementById('menu-items-root');
            if (rootContainer) {
                processContainer(rootContainer, null);
            }

            return items;
        }

        // Initialize on first load
        initMenuSortable();

        // Re-initialize after Livewire updates
        $wire.on('items-updated', () => {
            setTimeout(initMenuSortable, 100);
        });

        // Also listen for morph updates
        Livewire.hook('morph.updated', ({ component }) => {
            if (component.id === $wire.id) {
                setTimeout(initMenuSortable, 100);
            }
        });
    </script>
    @endscript
</div>
