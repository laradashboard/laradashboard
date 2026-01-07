@use('App\Services\PermissionService')

<x-card>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-primary/10 text-primary">
                <iconify-icon icon="lucide:shield" class="text-xl"></iconify-icon>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Role Details') }}</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-normal">{{ __('Configure the role name and permissions') }}</p>
            </div>
        </div>
    </x-slot>

    <x-slot name="headerRight">
        <x-buttons.submit-buttons cancelUrl="{{ route('admin.roles.index') }}" />
    </x-slot>

    <div class="max-w-md">
        <x-inputs.input
            name="name"
            id="name"
            :label="__('Role Name')"
            :value="old('name', $role->name ?? '')"
            :placeholder="__('Enter a descriptive role name')"
            required
            autofocus
        />
        <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">
            {{ __('Choose a clear, descriptive name that reflects the role\'s purpose.') }}
        </p>

        {!! Hook::applyFilters(RoleFilterHook::ROLE_FORM_AFTER_NAME, '', $role ?? null) !!}
    </div>
</x-card>

{{-- Permissions Section --}}
<x-card class="mt-6">
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 w-full">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-blue-50 text-primary dark:bg-blue-900/30 dark:text-primary">
                    <iconify-icon icon="lucide:key" class="text-xl"></iconify-icon>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Permissions') }}</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 font-normal">
                        {{ __('Select what this role can access') }}
                        <span id="permissionCounter" class="ml-1 font-medium text-primary"></span>
                    </p>
                </div>
            </div>

            {{-- Permission Search --}}
            <div
                class="relative flex items-center justify-center min-w-full md:min-w-[280px]"
                x-data="{ searchValue: '' }"
            >
                <span class="pointer-events-none absolute left-4 flex">
                    <iconify-icon icon="lucide:search" class="text-gray-500 dark:text-gray-400" width="20" height="20"></iconify-icon>
                </span>
                <input
                    type="text"
                    id="permissionSearch"
                    x-model="searchValue"
                    placeholder="{{ __('Search permissions...') }}"
                    class="form-control !pl-12 !pr-10 font-normal"
                    autocomplete="off"
                />
                <button
                    x-show="searchValue.length > 0"
                    x-cloak
                    @click="searchValue = ''; $dispatch('clear-search')"
                    id="clearSearch"
                    class="absolute right-2.5 top-1/2 inline-flex -translate-y-1/2 items-center justify-center rounded-full p-1 text-gray-400 transition-colors hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-700 dark:hover:text-gray-300"
                    aria-label="{{ __('Clear search') }}"
                    type="button"
                >
                    <iconify-icon icon="lucide:x" width="18" height="18"></iconify-icon>
                </button>
            </div>
        </div>
    </x-slot>

    <div class="space-y-2">
        {{-- Select All --}}
        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800/50 rounded-lg border border-gray-200 dark:border-gray-700">
            <label class="flex items-center gap-3 cursor-pointer">
                <input
                    type="checkbox"
                    id="checkPermissionAll"
                    class="form-checkbox h-5 w-5 text-primary rounded border-gray-300 dark:border-gray-600 focus:ring-primary/20"
                    @isset($role) {{ $roleService->roleHasPermissions($role, $all_permissions) ? 'checked' : '' }} @endisset
                >
                <div>
                    <span class="font-medium text-gray-900 dark:text-white">{{ __('Select All Permissions') }}</span>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Grant full access to all features') }}</p>
                </div>
            </label>
            <span class="text-sm text-gray-500 dark:text-gray-400" id="totalPermissionCount">
                {{ $all_permissions->count() }} {{ __('permissions') }}
            </span>
        </div>

        {{-- No Results Message --}}
        <div id="noSearchResults" class="hidden p-8 text-center">
            <iconify-icon icon="lucide:search-x" class="text-4xl text-gray-300 dark:text-gray-600 mb-2"></iconify-icon>
            <p class="text-gray-500 dark:text-gray-400">{{ __('No permissions found matching your search.') }}</p>
        </div>

        {{-- Permission Groups --}}
        <div id="permissionGroups" class="space-y-3 mt-4">
            {!! Hook::applyFilters(RoleFilterHook::ROLE_FORM_BEFORE_PERMISSION_GROUPS, '', $role ?? null) !!}
            @php $groupIndex = 0; @endphp
            @foreach ($permission_groups as $group)
                @php
                    $permissions = $roleService->getPermissionsByGroupName($group->name);
                    $groupHasAllPermissions = isset($role) && $roleService->roleHasPermissions($role, $permissions);
                    $groupIndex++;
                    $formattedGroupName = PermissionService::formatGroupName($group->name);
                @endphp
                <div
                    class="permission-group border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden transition-all"
                    data-group-name="{{ strtolower($group->name) }}"
                    data-group-formatted="{{ strtolower($formattedGroupName) }}"
                    x-data="{ expanded: false }"
                >
                    {{-- Group Header --}}
                    <div
                        class="flex items-center justify-between p-4 bg-white dark:bg-gray-800/30 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors"
                        @click="expanded = !expanded"
                    >
                        <div class="flex items-center gap-3">
                            {{-- Group Checkbox --}}
                            <label class="flex items-center cursor-pointer" @click.stop>
                                <input
                                    type="checkbox"
                                    id="group{{ $groupIndex }}Management"
                                    class="form-checkbox h-5 w-5 text-primary rounded border-gray-300 dark:border-gray-600 focus:ring-primary/20 group-checkbox"
                                    {{ $groupHasAllPermissions ? 'checked' : '' }}
                                >
                            </label>

                            {{-- Group Name --}}
                            <div>
                                <span class="font-medium text-gray-900 dark:text-white">
                                    {{ $formattedGroupName }}
                                </span>
                                <span class="ml-2 text-xs px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 permission-count">
                                    {{ $permissions->count() }} {{ trans_choice('permission|permissions', $permissions->count()) }}
                                </span>
                            </div>
                        </div>

                        {{-- Expand Icon --}}
                        <iconify-icon
                            icon="lucide:chevron-down"
                            class="text-gray-400 transition-transform duration-200"
                            :class="{ 'rotate-180': expanded }"
                        ></iconify-icon>
                    </div>

                    {{-- Group Permissions --}}
                    <div
                        x-show="expanded"
                        x-collapse
                        class="border-t border-gray-200 dark:border-gray-700"
                    >
                        <div
                            class="p-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 bg-gray-50/50 dark:bg-gray-900/30"
                            data-group="group{{ $groupIndex }}Management"
                        >
                            @foreach ($permissions as $permission)
                                @php
                                    $formattedName = PermissionService::formatPermissionName($permission->name);
                                @endphp
                                <label
                                    class="permission-item flex items-center gap-3 p-3 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 cursor-pointer hover:border-primary/50 hover:bg-primary/5 dark:hover:bg-primary/10 transition-all group"
                                    data-permission-name="{{ strtolower($permission->name) }}"
                                    data-permission-formatted="{{ strtolower($formattedName) }}"
                                >
                                    <input
                                        type="checkbox"
                                        id="checkPermission{{ $permission->id }}"
                                        name="permissions[]"
                                        value="{{ $permission->name }}"
                                        class="form-checkbox h-4 w-4 text-primary rounded border-gray-300 dark:border-gray-600 focus:ring-primary/20 permission-checkbox"
                                        @isset($role) {{ $role->hasPermissionTo($permission->name) ? 'checked' : '' }} @endisset
                                    >
                                    <div class="flex-1 min-w-0">
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-200 group-hover:text-primary transition-colors">
                                            {{ $formattedName }}
                                        </span>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
            {!! Hook::applyFilters(RoleFilterHook::ROLE_FORM_AFTER_PERMISSIONS, '', $role ?? null) !!}
        </div>
    </div>

    <x-slot name="footer">
        <div class="flex justify-end">
            <x-buttons.submit-buttons cancelUrl="{{ route('admin.roles.index') }}" />
        </div>
    </x-slot>
</x-card>

@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function () {
    const checkPermissionAll = document.getElementById("checkPermissionAll");
    const permissionSearch = document.getElementById("permissionSearch");
    const noResultsMsg = document.getElementById("noSearchResults");
    const permissionGroups = document.getElementById("permissionGroups");
    const permissionCounter = document.getElementById("permissionCounter");

    // Update permission counter
    function updatePermissionCounter() {
        const total = document.querySelectorAll('input[name="permissions[]"]').length;
        const checked = document.querySelectorAll('input[name="permissions[]"]:checked').length;
        permissionCounter.textContent = `(${checked}/${total})`;
    }

    // Initialize counter
    updatePermissionCounter();

    // Select All handler
    checkPermissionAll.addEventListener("change", function () {
        const isChecked = this.checked;
        document.querySelectorAll('input[type="checkbox"]').forEach((checkbox) => {
            if (!checkbox.closest('.permission-group.hidden')) {
                checkbox.checked = isChecked;
            }
        });
        updatePermissionCounter();
    });

    // Group checkbox handlers
    document.querySelectorAll('.group-checkbox').forEach((groupCheckbox) => {
        groupCheckbox.addEventListener("change", function () {
            const isChecked = this.checked;
            const groupId = this.id;
            const checkboxContainer = document.querySelector(`[data-group="${groupId}"]`);

            if (checkboxContainer) {
                checkboxContainer.querySelectorAll('.permission-checkbox').forEach((checkbox) => {
                    checkbox.checked = isChecked;
                });
            }

            updateSelectAllState();
            updatePermissionCounter();
        });
    });

    // Individual permission checkbox handlers
    document.querySelectorAll('.permission-checkbox').forEach((checkbox) => {
        checkbox.addEventListener("change", function () {
            const groupContainer = this.closest('[data-group]');
            if (!groupContainer) return;

            const groupId = groupContainer.getAttribute('data-group');
            const allCheckboxes = groupContainer.querySelectorAll('.permission-checkbox');
            const allChecked = Array.from(allCheckboxes).every(cb => cb.checked);

            const groupCheckbox = document.getElementById(groupId);
            if (groupCheckbox) {
                groupCheckbox.checked = allChecked;
            }

            updateSelectAllState();
            updatePermissionCounter();
        });
    });

    // Update Select All state
    function updateSelectAllState() {
        const totalPermissionCheckboxes = document.querySelectorAll('.permission-checkbox').length;
        const checkedPermissionCheckboxes = document.querySelectorAll('.permission-checkbox:checked').length;
        checkPermissionAll.checked = (totalPermissionCheckboxes > 0 && checkedPermissionCheckboxes === totalPermissionCheckboxes);
    }

    // Search functionality
    function performSearch() {
        const searchTerm = permissionSearch.value.toLowerCase().trim();
        let visibleGroups = 0;

        document.querySelectorAll('.permission-group').forEach((group) => {
            const groupName = group.dataset.groupName || '';
            const groupFormatted = group.dataset.groupFormatted || '';
            let groupHasVisiblePermissions = false;

            group.querySelectorAll('.permission-item').forEach((item) => {
                const permissionName = item.dataset.permissionName || '';
                const permissionFormatted = item.dataset.permissionFormatted || '';

                // Search in both raw and formatted names
                const isMatch = !searchTerm ||
                    permissionName.includes(searchTerm) ||
                    permissionFormatted.includes(searchTerm) ||
                    groupName.includes(searchTerm) ||
                    groupFormatted.includes(searchTerm);

                item.classList.toggle("hidden", !isMatch);
                if (isMatch) {
                    groupHasVisiblePermissions = true;
                }
            });

            group.classList.toggle("hidden", !groupHasVisiblePermissions);
            if (groupHasVisiblePermissions) {
                visibleGroups++;
                // Auto-expand groups with matches when searching
                if (searchTerm && group.__x) {
                    group.__x.$data.expanded = true;
                }
            }
        });

        // Show/hide no results message
        noResultsMsg.classList.toggle("hidden", visibleGroups > 0 || !searchTerm);
        permissionGroups.classList.toggle("hidden", visibleGroups === 0 && searchTerm);
    }

    // Listen for input changes
    permissionSearch.addEventListener("input", performSearch);

    // Listen for clear-search event from Alpine
    document.addEventListener("clear-search", function() {
        permissionSearch.value = "";
        performSearch();
        permissionSearch.focus();
    });

    // Initialize
    updateSelectAllState();
});
</script>
@endpush
