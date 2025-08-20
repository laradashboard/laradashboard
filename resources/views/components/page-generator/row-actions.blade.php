@props([
    'actions' => [],
    'item',
])

@if(count($actions) > 0)
    <x-buttons.action-buttons :label="__('Actions')" :show-label="false" align="right">
        @foreach($actions as $action)
            @if(!isset($action['condition']) || $action['condition']($item))
                @if(isset($action['type']) && $action['type'] === 'delete')
                    <div x-data="{ deleteModalOpen: false }">
                        <x-buttons.action-item
                            type="modal-trigger"
                            modal-target="deleteModalOpen"
                            :icon="$action['icon'] ?? 'trash'"
                            :label="$action['label'] ?? __('Delete')"
                            :class="$action['class'] ?? 'text-red-600 dark:text-red-400'"
                        />
                        
                        <x-modals.confirm-delete
                            :id="'delete-modal-' . $item->id"
                            :title="$action['modalTitle'] ?? __('Delete Record')"
                            :content="$action['modalContent'] ?? __('Are you sure you want to delete this record?')"
                            :formId="'delete-form-' . $item->id"
                            :formAction="$action['route']($item)"
                            modalTrigger="deleteModalOpen"
                            :cancelButtonText="__('No, cancel')"
                            :confirmButtonText="__('Yes, Confirm')"
                        />
                    </div>
                @else
                    <x-buttons.action-item
                        :href="isset($action['route']) ? $action['route']($item) : '#'"
                        :icon="$action['icon'] ?? 'pencil'"
                        :label="$action['label'] ?? ''"
                        :class="$action['class'] ?? ''"
                        @if(isset($action['onclick'])) 
                            onclick="{{ $action['onclick']($item) }}"
                        @endif
                        @if(isset($action['attributes']))
                            @foreach($action['attributes'] as $key => $value)
                                {{ $key }}="{{ is_callable($value) ? $value($item) : $value }}"
                            @endforeach
                        @endif
                    />
                @endif
            @endif
        @endforeach
    </x-buttons.action-buttons>
@endif