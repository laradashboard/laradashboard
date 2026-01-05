<?php

declare(strict_types=1);

namespace App\Livewire\Datatable;

use App\Collections\ModuleCollection;
use App\Models\Module;
use App\Services\Modules\ModuleService;
use App\Services\Modules\ModuleUpdateService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Spatie\QueryBuilder\QueryBuilder;

class ModuleDatatable extends Datatable
{
    public string $model = Module::class;

    public string $statusFilter = '';

    public array $queryString = [
        ...parent::QUERY_STRING_DEFAULTS,
        'statusFilter' => ['except' => ''],
    ];

    /**
     * Disable routes that don't exist for modules.
     */
    public array $disabledRoutes = ['view', 'edit', 'create'];

    /**
     * Disable default bulk actions - we use custom ones via renderAfterSearchbar().
     */
    public bool $enableBulkActions = false;

    /**
     * Override sort default since modules don't have created_at.
     */
    public string $sort = 'title';

    public string $direction = 'asc';

    protected ModuleService $moduleService;

    protected ModuleUpdateService $updateService;

    public function boot(): void
    {
        $this->moduleService = app(ModuleService::class);
        $this->updateService = app(ModuleUpdateService::class);

        // Auto-check for updates if no cached data exists
        if (config('laradashboard.updates.enabled', true) && ! $this->updateService->getCachedUpdates()) {
            $this->updateService->checkForUpdates();
        }
    }

    public function getSearchbarPlaceholder(): string
    {
        return __('Search modules by name, description...');
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    protected function getFilters(): array
    {
        return [
            [
                'id' => 'statusFilter',
                'label' => __('Status'),
                'filterLabel' => __('Filter by Status'),
                'icon' => 'lucide:filter',
                'allLabel' => __('All Statuses'),
                'options' => [
                    'enabled' => __('Enabled'),
                    'disabled' => __('Disabled'),
                ],
                'selected' => $this->statusFilter,
            ],
        ];
    }

    protected function getHeaders(): array
    {
        return [
            [
                'id' => 'title',
                'title' => __('Module'),
                'width' => null,
                'sortable' => true,
                'sortBy' => 'title',
                'searchable' => true,
            ],
            [
                'id' => 'description',
                'title' => __('Description'),
                'width' => null,
                'sortable' => false,
                'searchable' => true,
            ],
            [
                'id' => 'tags',
                'title' => __('Tags'),
                'width' => null,
                'sortable' => false,
            ],
            [
                'id' => 'version',
                'title' => __('Version'),
                'width' => '100px',
                'sortable' => true,
                'sortBy' => 'version',
            ],
            [
                'id' => 'status',
                'title' => __('Status'),
                'width' => '120px',
                'sortable' => true,
                'sortBy' => 'status',
            ],
            [
                'id' => 'actions',
                'title' => __('Actions'),
                'width' => '120px',
                'sortable' => false,
                'is_action' => true,
            ],
        ];
    }

    /**
     * Override to use ModuleCollection instead of QueryBuilder.
     */
    protected function getData(): LengthAwarePaginator
    {
        $query = ModuleCollection::query()
            ->search($this->search)
            ->searchable(['title', 'description', 'name']);

        // Apply status filter
        if ($this->statusFilter) {
            $statusBool = $this->statusFilter === 'enabled';
            $query->whereStatus($statusBool);
        }

        // Apply sorting
        if ($this->sort) {
            $query->orderBy($this->sort, $this->direction);
        }

        return $query->paginate($this->perPage);
    }

    /**
     * Override buildQuery since we don't use Eloquent.
     */
    protected function buildQuery(): QueryBuilder
    {
        // Not used for ModuleDatatable, but required by parent.
        // We override getData() instead.
        /** @phpstan-ignore-next-line Module is not an Eloquent model */
        return QueryBuilder::for(Module::class);
    }

    /**
     * Custom renderer for title column with icon.
     */
    public function renderTitleColumn(Module $module): Renderable
    {
        return view('backend.pages.modules.partials.module-title', compact('module'));
    }

    /**
     * Custom renderer for description column.
     */
    public function renderDescriptionColumn(Module $module): string
    {
        $description = $module->description;
        if (strlen($description) > 100) {
            $description = substr($description, 0, 100) . '...';
        }

        return '<span class="text-sm text-gray-600 dark:text-gray-300">' . e($description) . '</span>';
    }

    /**
     * Custom renderer for tags column.
     */
    public function renderTagsColumn(Module $module): Renderable
    {
        return view('backend.pages.modules.partials.module-tags', compact('module'));
    }

    /**
     * Custom renderer for version column.
     */
    public function renderVersionColumn(Module $module): string
    {
        return '<span class="text-sm text-gray-500 dark:text-gray-400">v' . e($module->version) . '</span>';
    }

    /**
     * Custom renderer for status column.
     */
    public function renderStatusColumn(Module $module): Renderable
    {
        return view('backend.pages.modules.partials.module-status', compact('module'));
    }

    /**
     * Custom renderer for actions column.
     */
    public function renderActionsColumn($module): Renderable
    {
        return view('backend.pages.modules.partials.module-actions', [
            'module' => $module,
            'permissions' => $this->getActionCellPermissions($module),
        ]);
    }

    public function renderBeforeFilters(): Renderable
    {
        return view('backend.pages.modules.partials.module-update-check-action');
    }

    /**
     * Override permissions for modules.
     */
    public function getActionCellPermissions($item): array
    {
        return [
            'view' => false, // Modules don't have a view page
            'edit' => false, // Modules don't have an edit page
            'delete' => true, // Always allow delete for now
            'toggle' => true, // Custom permission for toggle status
        ];
    }

    /**
     * Get delete action config using module name.
     */
    public function getDeleteAction($id): array
    {
        return [
            'url' => '',
            'method' => 'DELETE',
            'livewire' => true,
            'id' => $id, // This will be the module name
        ];
    }

    /**
     * Override delete to handle module name instead of numeric ID.
     */
    public function deleteItem($id): void
    {
        // Check demo mode
        if (config('app.demo_mode', false)) {
            $this->dispatch('notify', [
                'variant' => 'error',
                'title' => __('Demo Mode'),
                'message' => __('Module deletion is restricted in demo mode.'),
            ]);

            return;
        }

        $moduleName = $id;

        if (empty($moduleName)) {
            $this->dispatch('notify', [
                'variant' => 'error',
                'title' => __('Delete Failed'),
                'message' => __('No module selected for deletion.'),
            ]);

            return;
        }

        try {
            $this->moduleService->deleteModule($moduleName);

            // Flash message for after reload
            session()->flash('success', __('Module deleted successfully.'));

            // Reload the page
            $this->redirect(request()->header('Referer', route('admin.modules.index')), navigate: false);
        } catch (\Exception $exception) {
            Log::error("Failed to delete module {$moduleName}: " . $exception->getMessage());

            $this->dispatch('notify', [
                'variant' => 'error',
                'title' => __('Delete Failed'),
                'message' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * Override bulk delete for modules.
     */
    public function bulkDelete(): void
    {
        // Check demo mode
        if (config('app.demo_mode', false)) {
            $this->dispatch('notify', [
                'variant' => 'error',
                'title' => __('Demo Mode'),
                'message' => __('Module deletion is restricted in demo mode.'),
            ]);

            return;
        }

        $moduleNames = $this->selectedItems;

        if (empty($moduleNames)) {
            $this->dispatch('notify', [
                'variant' => 'error',
                'title' => __('Bulk Delete Failed'),
                'message' => __('No modules selected for deletion.'),
            ]);

            return;
        }

        $deletedCount = 0;
        $errors = [];

        foreach ($moduleNames as $moduleName) {
            try {
                $this->moduleService->deleteModule($moduleName);
                $deletedCount++;
            } catch (\Exception $e) {
                $errors[] = $moduleName;
                Log::error("Failed to delete module {$moduleName}: " . $e->getMessage());
            }
        }

        if ($deletedCount > 0) {
            $this->dispatch('notify', [
                'variant' => 'success',
                'title' => __('Bulk Delete Successful'),
                'message' => __(':count modules deleted successfully.', ['count' => $deletedCount]),
            ]);
        }

        if (! empty($errors)) {
            $this->dispatch('notify', [
                'variant' => 'error',
                'title' => __('Some Deletions Failed'),
                'message' => __('Failed to delete: :modules', ['modules' => implode(', ', $errors)]),
            ]);
        }

        $this->selectedItems = [];
        $this->dispatch('resetSelectedItems');
    }

    /**
     * Toggle module status.
     */
    public function toggleStatus(string $moduleName): void
    {
        // Check demo mode
        if (config('app.demo_mode', false)) {
            $this->dispatch('notify', [
                'variant' => 'error',
                'title' => __('Demo Mode'),
                'message' => __('Module enabling/disabling is restricted in demo mode.'),
            ]);

            return;
        }

        try {
            $newStatus = $this->moduleService->toggleModuleStatus($moduleName);

            // Flash message for after reload
            session()->flash('success', $newStatus
                ? __('Module :name has been enabled.', ['name' => $moduleName])
                : __('Module :name has been disabled.', ['name' => $moduleName]));

            // Reload the page to reflect sidebar/route changes
            $this->redirect(request()->header('Referer', route('admin.modules.index')), navigate: false);
        } catch (\Exception $e) {
            Log::error("Failed to toggle module {$moduleName}: " . $e->getMessage());

            $this->dispatch('notify', [
                'variant' => 'error',
                'title' => __('Status Update Failed'),
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Bulk activate selected modules.
     */
    public function bulkActivate(): void
    {
        // Check demo mode
        if (config('app.demo_mode', false)) {
            $this->dispatch('notify', [
                'variant' => 'error',
                'title' => __('Demo Mode'),
                'message' => __('Module enabling is restricted in demo mode.'),
            ]);

            return;
        }

        $moduleNames = $this->selectedItems;

        if (empty($moduleNames)) {
            $this->dispatch('notify', [
                'variant' => 'warning',
                'title' => __('Warning'),
                'message' => __('No modules selected.'),
            ]);

            return;
        }

        try {
            $results = $this->moduleService->bulkActivate($moduleNames);
            $successCount = count(array_filter($results));

            // Flash message for after reload
            session()->flash('success', __(':count modules activated successfully.', ['count' => $successCount]));

            // Reload the page to reflect sidebar/route changes
            $this->redirect(request()->header('Referer', route('admin.modules.index')), navigate: false);
        } catch (\Exception $e) {
            Log::error('Bulk activate failed: ' . $e->getMessage());

            $this->dispatch('notify', [
                'variant' => 'error',
                'title' => __('Bulk Activate Failed'),
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Bulk deactivate selected modules.
     */
    public function bulkDeactivate(): void
    {
        // Check demo mode
        if (config('app.demo_mode', false)) {
            $this->dispatch('notify', [
                'variant' => 'error',
                'title' => __('Demo Mode'),
                'message' => __('Module disabling is restricted in demo mode.'),
            ]);

            return;
        }

        $moduleNames = $this->selectedItems;

        if (empty($moduleNames)) {
            $this->dispatch('notify', [
                'variant' => 'warning',
                'title' => __('Warning'),
                'message' => __('No modules selected.'),
            ]);

            return;
        }

        try {
            $results = $this->moduleService->bulkDeactivate($moduleNames);
            $successCount = count(array_filter($results));

            // Flash message for after reload
            session()->flash('success', __(':count modules deactivated successfully.', ['count' => $successCount]));

            // Reload the page to reflect sidebar/route changes
            $this->redirect(request()->header('Referer', route('admin.modules.index')), navigate: false);
        } catch (\Exception $e) {
            Log::error('Bulk deactivate failed: ' . $e->getMessage());

            $this->dispatch('notify', [
                'variant' => 'error',
                'title' => __('Bulk Deactivate Failed'),
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Override to provide custom bulk actions.
     */
    public function renderAfterSearchbar(): Renderable
    {
        return view('backend.pages.modules.partials.module-bulk-actions');
    }

    /**
     * Refresh the datatable when module status changes.
     */
    #[On('module-status-changed')]
    public function refreshDatatable(): void
    {
        // This will re-render the component
    }

    /**
     * Render update notice row after module row (WordPress-style).
     */
    public function renderAfterRow(Module $module): ?Renderable
    {
        // Don't show update notices if update checking is disabled
        if (! config('laradashboard.updates.enabled', true)) {
            return null;
        }

        $updateInfo = $this->updateService->getModuleUpdate($module->name);

        if (! $updateInfo || ! $updateInfo['has_update']) {
            return null;
        }

        return view('backend.pages.modules.partials.module-update-notice', [
            'module' => $module,
            'updateInfo' => $updateInfo,
        ]);
    }

    /**
     * Update a module to the latest version.
     */
    public function updateModule(string $moduleName): void
    {
        // Check if update checking is enabled
        if (! config('laradashboard.updates.enabled', true)) {
            $this->dispatch('notify', [
                'variant' => 'info',
                'title' => __('Updates Disabled'),
                'message' => __('Module updates are currently disabled.'),
            ]);

            return;
        }

        // Check demo mode
        if (config('app.demo_mode', false)) {
            $this->dispatch('notify', [
                'variant' => 'error',
                'title' => __('Demo Mode'),
                'message' => __('Module updates are restricted in demo mode.'),
            ]);

            return;
        }

        try {
            $result = $this->updateService->downloadAndInstallUpdate($moduleName);

            if ($result['success']) {
                // Flash message for after reload
                session()->flash('success', $result['message']);

                // Reload the page to reflect changes
                $this->redirect(request()->header('Referer', route('admin.modules.index')), navigate: false);
            } else {
                $this->dispatch('notify', [
                    'variant' => 'error',
                    'title' => __('Update Failed'),
                    'message' => $result['message'],
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Failed to update module {$moduleName}: " . $e->getMessage());

            $this->dispatch('notify', [
                'variant' => 'error',
                'title' => __('Update Failed'),
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Check for module updates manually.
     */
    public function checkForUpdates(): void
    {
        // Check if update checking is enabled
        if (! config('laradashboard.updates.enabled', true)) {
            $this->dispatch('notify', [
                'variant' => 'info',
                'title' => __('Updates Disabled'),
                'message' => __('Module update checking is currently disabled. Set MODULE_UPDATE_CHECK_ENABLED=true in your .env file to enable.'),
            ]);

            return;
        }

        try {
            $result = $this->updateService->checkForUpdates(forceRefresh: true);

            if ($result['success']) {
                $updateCount = count($result['updates'] ?? []);

                $this->dispatch('notify', [
                    'variant' => 'success',
                    'title' => __('Update Check Complete'),
                    'message' => $updateCount > 0
                        ? __(':count update(s) available.', ['count' => $updateCount])
                        : __('All modules are up to date.'),
                ]);

                // Refresh the datatable to show updates
                $this->dispatch('$refresh');
            } else {
                $this->dispatch('notify', [
                    'variant' => 'error',
                    'title' => __('Update Check Failed'),
                    'message' => $result['error'] ?? __('Failed to check for updates.'),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to check for updates: ' . $e->getMessage());

            $this->dispatch('notify', [
                'variant' => 'error',
                'title' => __('Update Check Failed'),
                'message' => $e->getMessage(),
            ]);
        }
    }
}
