<div x-data="{ deleteModalOpen: false }">
    <x-buttons.action-buttons
        :label="__('Actions')"
        :show-label="false"
        icon="lucide:more-horizontal"
        align="right"
    >
        {{-- View Action --}}
        <x-buttons.action-item
            type="link"
            :href="route('admin.modules.show', $module->name)"
            icon="lucide:eye"
            :label="__('View')"
        />

        {{-- Toggle Status Action --}}
        <button
            type="button"
            wire:click="toggleStatus('{{ $module->name }}')"
            wire:loading.attr="disabled"
            wire:target="toggleStatus('{{ $module->name }}')"
            class="flex w-full items-center gap-2 px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-700 {{ $module->status ? 'text-orange-600 dark:text-orange-400' : 'text-green-600 dark:text-green-400' }}"
            x-on:click="isOpen = false; openedWithKeyboard = false"
            role="menuitem"
        >
            <span wire:loading.remove wire:target="toggleStatus('{{ $module->name }}')">
                <iconify-icon icon="{{ $module->status ? 'lucide:power-off' : 'lucide:power' }}" class="text-base"></iconify-icon>
            </span>
            <span wire:loading wire:target="toggleStatus('{{ $module->name }}')">
                <iconify-icon icon="lucide:loader-2" class="text-base animate-spin"></iconify-icon>
            </span>
            {{ $module->status ? __('Disable') : __('Enable') }}
        </button>

        {{-- Delete Action --}}
        <x-buttons.action-item
            type="modal-trigger"
            modal-target="deleteModalOpen"
            icon="lucide:trash"
            :label="__('Delete')"
            class="text-red-600 dark:text-red-400"
        />
    </x-buttons.action-buttons>

    {{-- Delete Confirmation Modal --}}
    <x-modals.confirm-delete
        id="delete-modal-{{ $module->name }}"
        :title="__('Delete Module')"
        :content="__('Are you sure you want to delete the module :name? This action cannot be undone.', ['name' => $module->title])"
        wireClick="deleteItem('{{ $module->name }}')"
        modalTrigger="deleteModalOpen"
        :cancelButtonText="__('No, Cancel')"
        :confirmButtonText="__('Yes, Delete')"
    />
</div>
