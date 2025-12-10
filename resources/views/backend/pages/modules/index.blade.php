<x-layouts.backend-layout :breadcrumbs="$breadcrumbs">
    <div
        id="modules-manager"
        x-data="{
            selectedModules: [],
            selectAll: false,
            bulkActionsDropdownOpen: false,
            viewMode: localStorage.getItem('modulesViewMode') || 'grid',

            toggleViewMode() {
                this.viewMode = this.viewMode === 'grid' ? 'list' : 'grid';
                localStorage.setItem('modulesViewMode', this.viewMode);
            },

            toggleSelectAll() {
                if (this.selectAll) {
                    this.selectedModules = [];
                } else {
                    this.selectedModules = [...document.querySelectorAll('.module-checkbox')].map(cb => cb.value);
                }
                this.selectAll = !this.selectAll;
            },

            isSelected(moduleName) {
                return this.selectedModules.includes(moduleName);
            },

            toggleSelection(moduleName) {
                if (this.isSelected(moduleName)) {
                    this.selectedModules = this.selectedModules.filter(m => m !== moduleName);
                } else {
                    this.selectedModules.push(moduleName);
                }
            }
        }"
        x-cloak
    >
        <div class="space-y-6">
            @if (empty($modules))
            <!-- Empty State -->
            <div class="flex flex-col items-center justify-center py-16 bg-gray-50 dark:bg-gray-800/50 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-700">
                <div class="w-20 h-20 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center mb-4">
                    <iconify-icon icon="lucide:package" class="text-4xl text-gray-400 dark:text-gray-500"></iconify-icon>
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">{{ __('No Modules Installed') }}</h3>
                <p class="text-gray-500 dark:text-gray-400 mb-6 text-center max-w-sm">
                    {{ __('Extend your application functionality by installing modules.') }}
                </p>
                <a href="{{ route('admin.modules.upload') }}" class="btn-primary">
                    <iconify-icon icon="lucide:package-plus" class="mr-2"></iconify-icon>
                    {{ __('Install Your First Module') }}
                </a>
            </div>
            @else
                <!-- Toolbar -->
                <div class="rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-white/[0.03]">
                    <div class="px-5 py-4 sm:px-6 sm:py-5 flex flex-col md:flex-row justify-between items-center gap-3">
                        <div class="flex items-center gap-3">
                            <!-- Select All Checkbox -->
                            <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300 cursor-pointer">
                                <input type="checkbox"
                                    x-model="selectAll"
                                    @click="toggleSelectAll()"
                                    class="form-checkbox">
                                <span>{{ __('Select All') }}</span>
                            </label>

                            <!-- Selected Count Badge -->
                            <span x-show="selectedModules.length > 0"
                                class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full dark:bg-blue-900/20 dark:text-primary">
                                <span x-text="selectedModules.length"></span> {{ __('selected') }}
                            </span>
                        </div>

                        <div class="flex items-center gap-3">
                            <!-- Bulk Actions dropdown -->
                            <div class="flex items-center justify-center relative" x-show="selectedModules.length > 0">
                                <button @click="bulkActionsDropdownOpen = !bulkActionsDropdownOpen"
                                    class="btn-secondary flex items-center justify-center gap-2 text-sm" type="button">
                                    <iconify-icon icon="lucide:more-vertical"></iconify-icon>
                                    <span>{{ __('Bulk Actions') }}</span>
                                    <iconify-icon icon="lucide:chevron-down"></iconify-icon>
                                </button>

                                <!-- Bulk Actions dropdown menu -->
                                <div x-show="bulkActionsDropdownOpen" @click.away="bulkActionsDropdownOpen = false"
                                    x-transition:enter="transition ease-out duration-100"
                                    x-transition:enter-start="transform opacity-0 scale-95"
                                    x-transition:enter-end="transform opacity-100 scale-100"
                                    x-transition:leave="transition ease-in duration-75"
                                    x-transition:leave-start="transform opacity-100 scale-100"
                                    x-transition:leave-end="transform opacity-0 scale-95"
                                    class="absolute top-full right-0 z-10 w-48 p-2 bg-white rounded-md shadow-lg dark:bg-gray-700 mt-2">
                                    <ul class="space-y-1">
                                        <li>
                                            <button @click="bulkActivateModules(); bulkActionsDropdownOpen = false"
                                                class="w-full flex items-center gap-2 text-sm text-green-600 dark:text-green-400 hover:bg-green-50 dark:hover:bg-green-900/20 px-3 py-2 rounded transition-colors duration-300">
                                                <iconify-icon icon="lucide:toggle-right"></iconify-icon>
                                                {{ __('Activate Selected') }}
                                            </button>
                                        </li>
                                        <li>
                                            <button @click="bulkDeactivateModules(); bulkActionsDropdownOpen = false"
                                                class="w-full flex items-center gap-2 text-sm text-orange-600 dark:text-orange-400 hover:bg-orange-50 dark:hover:bg-orange-900/20 px-3 py-2 rounded transition-colors duration-300">
                                                <iconify-icon icon="lucide:toggle-left"></iconify-icon>
                                                {{ __('Deactivate Selected') }}
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <!-- View Mode Toggle -->
                            <button @click="toggleViewMode()" class="btn-secondary flex items-center gap-2">
                                <iconify-icon :icon="viewMode === 'grid' ? 'lucide:list' : 'lucide:grid-3x3'"
                                    class="text-sm"></iconify-icon>
                                <span class="hidden sm:inline"
                                    x-text="viewMode === 'grid' ? '{{ __('List View') }}' : '{{ __('Grid View') }}'"></span>
                            </button>
                        </div>
                    </div>

                    <!-- Grid View -->
                    <div x-show="viewMode === 'grid'" class="border-t border-gray-100 dark:border-gray-800 p-5 sm:p-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach ($modules as $module)
                                <div tabindex="0"
                                    class="rounded-md border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-primary dark:focus:ring-brand-800 transition-all duration-200 hover:shadow-md"
                                    :class="{ 'ring-2 ring-primary dark:ring-brand-800': isSelected('{{ $module->name }}') }">
                                    <div class="flex justify-between" x-data="{ deleteModalOpen: false, errorModalOpen: false, errorMessage: '', dropdownOpen: false }">
                                        <div class="py-3 flex items-start gap-3">
                                            <!-- Selection Checkbox -->
                                            <input type="checkbox"
                                                value="{{ $module->name }}"
                                                class="module-checkbox form-checkbox mt-1"
                                                :checked="isSelected('{{ $module->name }}')"
                                                @change="toggleSelection('{{ $module->name }}')">
                                            <div>
                                                <h2>
                                                    <i class="bi {{ $module->icon }} text-3xl text-gray-500 dark:text-gray-300"></i>
                                                </h2>
                                                <h3 class="text-lg font-medium text-gray-700 dark:text-white">
                                                    {{ $module->title }}
                                                </h3>
                                            </div>
                                        </div>

                                        <div class="relative">
                                            <button @click="dropdownOpen = !dropdownOpen" class="inline-flex items-center h-9 p-2 text-sm font-medium text-center text-gray-700 bg-white rounded-md hover:bg-gray-100 focus:ring-4 focus:outline-none dark:text-white focus:ring-gray-50 dark:bg-gray-800 dark:hover:bg-gray-700 dark:focus:ring-gray-600" type="button">
                                                <iconify-icon icon="lucide:more-vertical"></iconify-icon>
                                            </button>

                                            <div x-show="dropdownOpen"
                                                @click.away="dropdownOpen = false"
                                                x-transition:enter="transition ease-out duration-100"
                                                x-transition:enter-start="transform opacity-0 scale-95"
                                                x-transition:enter-end="transform opacity-100 scale-100"
                                                x-transition:leave="transition ease-in duration-75"
                                                x-transition:leave-start="transform opacity-100 scale-100"
                                                x-transition:leave-end="transform opacity-0 scale-95"
                                                class="absolute top-full right-0 z-10 w-44 bg-white divide-y divide-gray-100 rounded-md shadow-lg dark:bg-gray-700 dark:divide-gray-600 mt-2">
                                                <ul class="py-2 text-sm text-gray-700 dark:text-gray-200">
                                                    <li>
                                                        <button
                                                            @click="deleteModalOpen = true; dropdownOpen = false"
                                                            class="flex items-center w-full px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white text-left"
                                                        >
                                                            <iconify-icon icon="lucide:trash" class="mr-2 text-red-500"></iconify-icon>
                                                            {{ __('Delete') }}
                                                        </button>
                                                    </li>
                                                    <li>
                                                        <button
                                                            @click="toggleModuleStatus('{{ $module->name }}', $event); dropdownOpen = false"
                                                            class="flex items-center w-full px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white text-left"
                                                        >
                                                            <iconify-icon icon="{{ $module->status ? 'lucide:toggle-left' : 'lucide:toggle-right' }}" class="mr-2 {{ $module->status ? 'text-green-500' : 'text-gray-500' }}"></iconify-icon>
                                                            {{ $module->status ? __('Disable') : __('Enable') }}
                                                        </button>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>

                                        <x-modals.confirm-delete
                                            id="delete-modal-{{ $module->name }}"
                                            title="{{ __('Delete Module') }}"
                                            content="{{ __('Are you sure you want to delete this module?') }}"
                                            formId="delete-form-{{ $module->name }}"
                                            formAction="{{ route('admin.modules.delete', $module->name) }}"
                                            modalTrigger="deleteModalOpen"
                                            cancelButtonText="{{ __('No, Cancel') }}"
                                            confirmButtonText="{{ __('Yes, Confirm') }}"
                                        />

                                        <x-modals.error-message
                                            id="error-modal-{{ $module->name }}"
                                            title="{{ __('Operation Failed') }}"
                                            modalTrigger="errorModalOpen"
                                        />
                                    </div>
                                    <p class="text-sm text-gray-600 dark:text-gray-300">{{ $module->description }}</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-300">
                                        {{ __('Tags:') }}
                                        @forelse ($module->tags as $tag)
                                            <span class="badge">{{ $tag }}</span>
                                        @empty
                                            {{ __('N/A') }}
                                        @endforelse
                                    </p>
                                    <div class="mt-4 flex items-center justify-between">
                                        <span class="text-sm font-medium {{ $module->status ? 'text-green-500' : 'text-red-500' }}">
                                            {{ $module->status ? __('Enabled') : __('Disabled') }}
                                        </span>
                                        <span class="text-xs text-gray-400">v{{ $module->version ?? '1.0.0' }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- List View -->
                    <div x-show="viewMode === 'list'" class="border-t border-gray-100 dark:border-gray-800 overflow-x-auto">
                        <table class="table">
                            <thead class="table-thead">
                                <tr class="table-tr">
                                    <th width="3%" class="table-thead-th">
                                        <input type="checkbox"
                                            x-model="selectAll"
                                            @click="toggleSelectAll()"
                                            class="form-checkbox">
                                    </th>
                                    <th class="table-thead-th">{{ __('Module') }}</th>
                                    <th class="table-thead-th">{{ __('Description') }}</th>
                                    <th class="table-thead-th">{{ __('Tags') }}</th>
                                    <th class="table-thead-th">{{ __('Version') }}</th>
                                    <th class="table-thead-th">{{ __('Status') }}</th>
                                    <th class="table-thead-th table-thead-th-last">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="table-tbody">
                                @foreach ($modules as $module)
                                    <tr class="table-tr" x-data="{ deleteModalOpen: false, errorModalOpen: false, errorMessage: '' }">
                                        <td class="table-td table-td-checkbox">
                                            <input type="checkbox"
                                                value="{{ $module->name }}"
                                                class="module-checkbox form-checkbox"
                                                :checked="isSelected('{{ $module->name }}')"
                                                @change="toggleSelection('{{ $module->name }}')">
                                        </td>
                                        <td class="table-td">
                                            <div class="flex items-center gap-3">
                                                <i class="bi {{ $module->icon }} text-2xl text-gray-500 dark:text-gray-300"></i>
                                                <span class="font-medium text-gray-900 dark:text-white">{{ $module->title }}</span>
                                            </div>
                                        </td>
                                        <td class="table-td">
                                            <span class="text-sm text-gray-600 dark:text-gray-300 line-clamp-2">{{ $module->description }}</span>
                                        </td>
                                        <td class="table-td">
                                            @forelse ($module->tags as $tag)
                                                <span class="badge">{{ $tag }}</span>
                                            @empty
                                                <span class="text-gray-400">{{ __('N/A') }}</span>
                                            @endforelse
                                        </td>
                                        <td class="table-td">
                                            <span class="text-sm text-gray-500">v{{ $module->version ?? '1.0.0' }}</span>
                                        </td>
                                        <td class="table-td">
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $module->status ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400' : 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400' }}">
                                                {{ $module->status ? __('Enabled') : __('Disabled') }}
                                            </span>
                                        </td>
                                        <td class="table-td text-center">
                                            <div class="flex items-center justify-center gap-2">
                                                <button
                                                    @click="toggleModuleStatus('{{ $module->name }}', $event)"
                                                    class="{{ $module->status ? 'text-orange-400 hover:text-orange-600' : 'text-green-400 hover:text-green-600' }}"
                                                    title="{{ $module->status ? __('Disable') : __('Enable') }}">
                                                    <iconify-icon icon="{{ $module->status ? 'lucide:toggle-left' : 'lucide:toggle-right' }}" class="text-lg"></iconify-icon>
                                                </button>
                                                <button
                                                    @click="deleteModalOpen = true"
                                                    class="text-red-400 hover:text-red-600"
                                                    title="{{ __('Delete') }}">
                                                    <iconify-icon icon="lucide:trash" class="text-lg"></iconify-icon>
                                                </button>
                                            </div>

                                            <x-modals.confirm-delete
                                                id="delete-modal-list-{{ $module->name }}"
                                                title="{{ __('Delete Module') }}"
                                                content="{{ __('Are you sure you want to delete this module?') }}"
                                                formId="delete-form-list-{{ $module->name }}"
                                                formAction="{{ route('admin.modules.delete', $module->name) }}"
                                                modalTrigger="deleteModalOpen"
                                                cancelButtonText="{{ __('No, Cancel') }}"
                                                confirmButtonText="{{ __('Yes, Confirm') }}"
                                            />

                                            <x-modals.error-message
                                                id="error-modal-list-{{ $module->name }}"
                                                title="{{ __('Operation Failed') }}"
                                                modalTrigger="errorModalOpen"
                                            />
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="px-5 py-4 border-t border-gray-100 dark:border-gray-800">
                        {{ $modules->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
    <script>
        const csrf_token = '{{ csrf_token() }}';

        function toggleModuleStatus(moduleName, event) {
            const moduleElement = event.target.closest('[x-data]');
            const Alpine = window.Alpine;

            fetch(`/admin/modules/toggle-status/${moduleName}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf_token,
                    'Content-Type': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (window.showToast) {
                        window.showToast('success', '{{ __("Success") }}', data.message || '{{ __("Module status updated successfully") }}');
                    }
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    if (moduleElement && Alpine) {
                        const component = Alpine.$data(moduleElement);
                        component.errorMessage = data.message || '{{ __("An error occurred while processing your request.") }}';
                        component.errorModalOpen = true;
                    }
                }
            })
            .catch(error => {
                if (moduleElement && Alpine) {
                    const component = Alpine.$data(moduleElement);
                    component.errorMessage = '{{ __("Network error. Please check your connection and try again.") }}';
                    component.errorModalOpen = true;
                }
            });
        }

        function bulkActivateModules() {
            const Alpine = window.Alpine;
            const container = document.getElementById('modules-manager');
            const selectedModules = Alpine.$data(container).selectedModules;

            if (!selectedModules || selectedModules.length === 0) {
                if (window.showToast) {
                    window.showToast('warning', '{{ __("Warning") }}', '{{ __("No modules selected.") }}');
                }
                return;
            }

            fetch('/admin/modules/bulk-activate', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf_token,
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ modules: selectedModules })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (window.showToast) {
                        window.showToast('success', '{{ __("Success") }}', data.message);
                    }
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    if (window.showToast) {
                        window.showToast('error', '{{ __("Error") }}', data.message || '{{ __("An error occurred while processing your request.") }}');
                    }
                }
            })
            .catch(error => {
                if (window.showToast) {
                    window.showToast('error', '{{ __("Error") }}', '{{ __("Network error. Please check your connection and try again.") }}');
                }
            });
        }

        function bulkDeactivateModules() {
            const Alpine = window.Alpine;
            const container = document.getElementById('modules-manager');
            const selectedModules = Alpine.$data(container).selectedModules;

            if (!selectedModules || selectedModules.length === 0) {
                if (window.showToast) {
                    window.showToast('warning', '{{ __("Warning") }}', '{{ __("No modules selected.") }}');
                }
                return;
            }

            fetch('/admin/modules/bulk-deactivate', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf_token,
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ modules: selectedModules })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (window.showToast) {
                        window.showToast('success', '{{ __("Success") }}', data.message);
                    }
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    if (window.showToast) {
                        window.showToast('error', '{{ __("Error") }}', data.message || '{{ __("An error occurred while processing your request.") }}');
                    }
                }
            })
            .catch(error => {
                if (window.showToast) {
                    window.showToast('error', '{{ __("Error") }}', '{{ __("Network error. Please check your connection and try again.") }}');
                }
            });
        }
    </script>
    @endpush
</x-layouts.backend-layout>
