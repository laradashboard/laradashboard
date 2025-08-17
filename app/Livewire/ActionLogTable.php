<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Concerns\HasDataTableFeatures;
use App\Models\ActionLog;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\Traits\WithExport;

class ActionLogTable extends PowerGridComponent
{
    use HasDataTableFeatures, WithExport;

    public string $tableName = 'action-log-table';

    public function datasource(): Builder
    {
        $query = ActionLog::query()
            ->with(['user'])
            ->select('action_logs.*');

        return $this->applyQueryFilters($query);
    }

    public function relationSearch(): array
    {
        return [
            'user' => ['first_name', 'last_name', 'email'],
        ];
    }

    public function columns(): array
    {
        return $this->getExtensibleColumns();
    }

    public function filters(): array
    {
        return $this->getExtensibleFilters();
    }

    public function actions(): array
    {
        return $this->getExtensibleActions();
    }

    // Implementation of abstract methods from HasDataTableFeatures

    protected function getBaseColumns(): array
    {
        return [
            $this->formatIdColumn(),

            Column::make('User', 'user_id')
                ->sortable()
                ->searchable()
                ->format(function ($value, $row) {
                    if (!$row->user) {
                        return '<span class="text-gray-500 dark:text-gray-400">System</span>';
                    }
                    
                    return '<div class="flex items-center">
                        <img src="' . $row->user->avatar_url . '" alt="' . $row->user->full_name . '" class="w-8 h-8 rounded-full mr-2">
                        <div class="flex flex-col">
                            <span class="font-medium text-gray-900 dark:text-white">' . $row->user->full_name . '</span>
                            <span class="text-xs text-gray-500 dark:text-gray-400">' . $row->user->email . '</span>
                        </div>
                    </div>';
                }),

            Column::make('Action', 'action_type')
                ->sortable()
                ->searchable()
                ->format(function ($value) {
                    $colors = [
                        'CREATED' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                        'UPDATED' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                        'DELETED' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                        'VIEWED' => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200',
                        'LOGIN' => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
                        'LOGOUT' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                    ];
                    
                    $colorClass = $colors[$value] ?? $colors['VIEWED'];
                    
                    return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ' . $colorClass . '">' . $value . '</span>';
                }),

            Column::make('Resource', 'resource_type')
                ->sortable()
                ->searchable()
                ->format(function ($value, $row) {
                    $resourceName = class_basename($value);
                    $resourceId = $row->resource_id ? " #{$row->resource_id}" : '';
                    
                    return '<div class="flex flex-col">
                        <span class="font-medium text-gray-900 dark:text-white">' . $resourceName . $resourceId . '</span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">' . $value . '</span>
                    </div>';
                }),

            Column::make('Description', 'description')
                ->sortable()
                ->searchable()
                ->format(function ($value) {
                    return '<span class="text-sm text-gray-600 dark:text-gray-300">' . \Str::limit($value, 50) . '</span>';
                }),

            Column::make('IP Address', 'ip_address')
                ->sortable()
                ->format(function ($value) {
                    return '<span class="font-mono text-xs text-gray-600 dark:text-gray-300">' . $value . '</span>';
                }),

            $this->formatCreatedAtColumn(),
        ];
    }

    protected function getBaseActions(): array
    {
        $actions = [];

        if ($this->canPerformAction('view')) {
            $actions[] = Button::make('view', 'View Details')
                ->class('btn-sm btn-info inline-flex items-center px-3 py-1.5 text-xs font-medium text-blue-700 bg-blue-50 border border-blue-300 rounded-md hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-blue-500')
                ->icon('heroicon-o-eye')
                ->method('viewActionLog');
        }

        return $actions;
    }

    protected function getBaseFilters(): array
    {
        $actionTypes = ActionLog::distinct('action_type')
            ->pluck('action_type')
            ->map(fn ($type) => ['name' => $type, 'value' => $type])
            ->toArray();

        $resourceTypes = ActionLog::distinct('resource_type')
            ->pluck('resource_type')
            ->map(fn ($type) => ['name' => class_basename($type), 'value' => $type])
            ->toArray();

        return [
            Filter::inputText('search')
                ->placeholder('Search action logs...'),

            Filter::select('action_type')
                ->dataSource($actionTypes)
                ->optionLabel('name')
                ->optionValue('value')
                ->placeholder('Filter by action'),

            Filter::select('resource_type')
                ->dataSource($resourceTypes)
                ->optionLabel('name')
                ->optionValue('value')
                ->placeholder('Filter by resource'),

            Filter::datepicker('created_at')
                ->label('Created Date'),

            Filter::dateTimePicker('date_range')
                ->label('Date Range'),
        ];
    }

    protected function applyBaseQueryFilters($query)
    {
        // Apply search filter
        if ($search = request('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%")
                  ->orWhere('action_type', 'like', "%{$search}%")
                  ->orWhere('resource_type', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('first_name', 'like', "%{$search}%")
                               ->orWhere('last_name', 'like', "%{$search}%")
                               ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Apply action type filter
        if ($actionType = request('action_type')) {
            $query->where('action_type', $actionType);
        }

        // Apply resource type filter
        if ($resourceType = request('resource_type')) {
            $query->where('resource_type', $resourceType);
        }

        return $query;
    }

    protected function getHookPrefix(): string
    {
        return 'action_log';
    }

    protected function getRouteName(): string
    {
        return 'action-logs';
    }

    protected function getModelClass(): string
    {
        return ActionLog::class;
    }

    protected function getPermissionPrefix(): string
    {
        return 'action_log';
    }

    /**
     * View action log details
     */
    public function viewActionLog(int $id): void
    {
        $actionLog = ActionLog::with(['user'])->findOrFail($id);

        $this->dispatchBrowserEvent('show-action-log-modal', [
            'actionLog' => [
                'id' => $actionLog->id,
                'user' => $actionLog->user ? [
                    'name' => $actionLog->user->full_name,
                    'email' => $actionLog->user->email,
                    'avatar' => $actionLog->user->avatar_url,
                ] : null,
                'action_type' => $actionLog->action_type,
                'resource_type' => $actionLog->resource_type,
                'resource_id' => $actionLog->resource_id,
                'description' => $actionLog->description,
                'ip_address' => $actionLog->ip_address,
                'user_agent' => $actionLog->user_agent ?? 'N/A',
                'properties' => $actionLog->properties ?? [],
                'created_at' => $actionLog->created_at->format('M j, Y H:i:s'),
            ]
        ]);
    }
}