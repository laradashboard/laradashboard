@php
    $moduleConfig = [];
    $modulePath = base_path('modules/' . $module->name . '/module.json');
    if (file_exists($modulePath)) {
        $moduleConfig = json_decode(file_get_contents($modulePath), true) ?? [];
    }
    $pricing = $moduleConfig['pricing'] ?? 'free';
    $isPaidModule = in_array($pricing, ['paid', 'both']);

    // Check if license is already activated
    $licenseService = app(\App\Services\LicenseVerificationService::class);
    $storedLicense = $licenseService->getStoredLicense($module->name);
    $hasActiveLicense = !empty($storedLicense['license_key']);

    // Check for available updates
    $updateService = app(\App\Services\Modules\ModuleUpdateService::class);
    $updateInfo = $updateService->getModuleUpdate($module->name);
    $hasUpdate = $updateInfo && ($updateInfo['has_update'] ?? false);
@endphp

<div
    x-data="{ deleteModalOpen: false, licenseModalOpen: false, updateModalOpen: false, updating: false }"
    x-on:open-license-modal-{{ $module->name }}.window="licenseModalOpen = true"
    x-on:open-delete-modal-{{ $module->name }}.window="deleteModalOpen = true"
    x-on:open-update-modal-{{ $module->name }}.window="updateModalOpen = true"
>
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

        {{-- Update Action (when update is available) --}}
        @if($hasUpdate)
            <button
                type="button"
                class="flex w-full items-center gap-2 px-4 py-2 text-sm text-blue-600 dark:text-blue-400 hover:bg-gray-100 dark:hover:bg-gray-700"
                x-on:click="isOpen = false; openedWithKeyboard = false; $dispatch('open-update-modal-{{ $module->name }}')"
                role="menuitem"
            >
                <iconify-icon icon="lucide:arrow-up-circle" class="text-base"></iconify-icon>
                {{ __('Update to v:version', ['version' => $updateInfo['latest_version']]) }}
            </button>
        @endif

        {{-- License Action (only for paid/freemium modules) --}}
        @if($isPaidModule)
            @if($hasActiveLicense)
                {{-- Deactivate License --}}
                <button
                    type="button"
                    class="flex w-full items-center gap-2 px-4 py-2 text-sm text-primary dark:text-primary hover:bg-gray-100 dark:hover:bg-gray-700"
                    x-on:click="isOpen = false; openedWithKeyboard = false; $dispatch('open-license-modal-{{ $module->name }}')"
                    role="menuitem"
                >
                    <iconify-icon icon="lucide:key-round" class="text-base"></iconify-icon>
                    {{ __('Manage License') }}
                </button>
            @else
                {{-- Activate License --}}
                <button
                    type="button"
                    class="flex w-full items-center gap-2 px-4 py-2 text-sm text-purple-600 dark:text-purple-400 hover:bg-gray-100 dark:hover:bg-gray-700"
                    x-on:click="isOpen = false; openedWithKeyboard = false; $dispatch('open-license-modal-{{ $module->name }}')"
                    role="menuitem"
                >
                    <iconify-icon icon="lucide:key" class="text-base"></iconify-icon>
                    {{ __('Activate License') }}
                </button>
            @endif
        @endif

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
        <button
            type="button"
            class="flex w-full items-center gap-2 px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-gray-100 dark:hover:bg-gray-700"
            x-on:click="isOpen = false; openedWithKeyboard = false; $dispatch('open-delete-modal-{{ $module->name }}')"
            role="menuitem"
        >
            <iconify-icon icon="lucide:trash" class="text-base"></iconify-icon>
            {{ __('Delete') }}
        </button>
    </x-buttons.action-buttons>

    {{-- License Activation Modal (only for paid/freemium modules) --}}
    @if($isPaidModule)
        <x-modals.license-activation
            id="license-modal-{{ $module->name }}"
            :module-slug="$module->name"
            :module-name="$module->title"
            modalTrigger="licenseModalOpen"
        />
    @endif

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

    {{-- Update Confirmation Modal --}}
    @if($hasUpdate)
        <x-modals.module-update
            id="update-modal-{{ $module->name }}"
            :module="$module"
            :updateInfo="$updateInfo"
            modalTrigger="updateModalOpen"
        />
    @endif
</div>
