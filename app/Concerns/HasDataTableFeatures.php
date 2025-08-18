<?php

declare(strict_types=1);

namespace App\Concerns;

use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Components\SetUp\Exportable;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\Facades\Rule;

trait HasDataTableFeatures
{
    /**
     * Setup the data table with common configurations
     */
    public function setUp(): array
    {
        return [
            // Exportable::exportable('export')
            //    ->striped()
            //    ->type(Exportable::TYPE_XLS, Exportable::TYPE_CSV),
            PowerGrid::header()
                ->showSearchInput()
                ->showSoftDeletes(showMessage: true),
            PowerGrid::footer()
                ->showPerPage()
                ->showRecordCount(),
        ];
    }

    /**
     * Get extensible columns with hook support
     */
    protected function getExtensibleColumns(): array
    {
        $baseColumns = $this->getBaseColumns();

        // Apply hook filters for column customization
        $columns = ld_apply_filters($this->getHookPrefix() . '_table_columns', $baseColumns);

        return $columns;
    }

    /**
     * Get extensible actions with hook support
     */
    protected function getExtensibleActions(): array
    {
        $baseActions = $this->getBaseActions();

        // Apply hook filters for action customization
        $actions = ld_apply_filters($this->getHookPrefix() . '_table_actions', $baseActions);

        return $actions;
    }

    /**
     * Get extensible filters with hook support
     */
    protected function getExtensibleFilters(): array
    {
        $baseFilters = $this->getBaseFilters();

        // Apply hook filters for filter customization
        $filters = ld_apply_filters($this->getHookPrefix() . '_table_filters', $baseFilters);

        return $filters;
    }

    /**
     * Apply query filters with hook support
     */
    protected function applyQueryFilters($query)
    {
        // Apply base filters first
        $query = $this->applyBaseQueryFilters($query);

        // Apply hook filters for custom query modifications
        $query = ld_apply_filters($this->getHookPrefix() . '_table_query', $query);

        return $query;
    }

    /**
     * Common action buttons
     */
    protected function getCommonActionButtons(): array
    {
        return [
            Button::make('edit', 'Edit')
                ->class('btn-sm btn-secondary')
                ->icon('heroicon-o-pencil')
                ->route('admin.' . $this->getRouteName() . '.edit', fn ($row) => $row->id),

            Button::make('delete', 'Delete')
                ->class('btn-sm btn-danger')
                ->icon('heroicon-o-trash')
                ->confirm('Are you sure you want to delete this item?')
                ->method('delete')
                ->emit('delete', ['id' => 'id']),
        ];
    }

    /**
     * Common filters
     */
    protected function getCommonFilters(): array
    {
        return [
            Filter::datepicker('created_at')
                ->label('Created Date'),

            Filter::inputText('search')
                ->operators(['contains'])
                ->placeholder('Search...'),
        ];
    }

    /**
     * Standard column formatting
     */
    protected function formatIdColumn(): Column
    {
        return Column::make('id', 'id')
            ->sortable()
            ->searchable()
            ->hidden();
    }

    protected function formatCreatedAtColumn(): Column
    {
        return Column::make('Created At', 'created_at')
            ->sortable()
            ->searchable();
        // ->format(fn ($value) => $value ? $value->format('M j, Y H:i') : '');
    }

    // protected function formatUpdatedAtColumn(): Column
    // {
    //     return Column::make('Updated At', 'updated_at')
    //         ->sortable()
    //         ->format(fn ($value) => $value ? $value->format('M j, Y H:i') : '');
    // }

    /**
     * Bulk actions support
     */
    protected function getBulkActions(): array
    {
        return [
            Button::make('bulk-delete', 'Delete Selected')
                ->class('btn-danger')
                ->icon('heroicon-o-trash')
                ->confirm('Are you sure you want to delete the selected items?')
                ->method('bulkDelete'),
        ];
    }

    /**
     * Handle bulk delete action
     */
    public function bulkDelete(): void
    {
        $this->validate([
            'checkboxValues' => 'required|array|min:1',
        ]);

        $model = $this->getModelClass();
        $items = $model::whereIn('id', $this->checkboxValues)->get();

        foreach ($items as $item) {
            // Apply delete hook filters
            $item = ld_apply_filters($this->getHookPrefix() . '_before_delete', $item);
            $item->delete();
            ld_do_action($this->getHookPrefix() . '_after_delete', $item);
        }

        $this->checkboxValues = [];

        session()->flash('success', __(':count items deleted successfully', [
            'count' => count($this->checkboxValues),
        ]));
    }

    /**
     * Handle individual delete action
     */
    public function delete($id): void
    {
        $model = $this->getModelClass();
        $item = $model::findOrFail($id);

        // Apply delete hook filters
        $item = ld_apply_filters($this->getHookPrefix() . '_before_delete', $item);
        $item->delete();
        ld_do_action($this->getHookPrefix() . '_after_delete', $item);

        session()->flash('success', __('Item deleted successfully'));
    }

    /**
     * Authorization check for actions
     */
    protected function canPerformAction(string $action, $item = null): bool
    {
        $permission = $this->getPermissionPrefix() . '.' . $action;

        return auth()->user()->can($permission);
    }

    /**
     * Apply styling rules
     */
    protected function getStylingRules(): array
    {
        return [
            Rule::rows()
                ->when(fn ($row) => $row->deleted_at !== null)
                ->setAttribute('class', 'bg-red-50 dark:bg-red-900/20'),
        ];
    }

    // Abstract methods that must be implemented by child classes
    abstract protected function getBaseColumns(): array;
    abstract protected function getBaseActions(): array;
    abstract protected function getBaseFilters(): array;
    abstract protected function applyBaseQueryFilters($query);
    abstract protected function getHookPrefix(): string;
    abstract protected function getRouteName(): string;
    abstract protected function getModelClass(): string;
    abstract protected function getPermissionPrefix(): string;
}
