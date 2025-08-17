<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Concerns\HasDataTableFeatures;
use App\Models\Permission;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\Traits\WithExport;

class PermissionTable extends PowerGridComponent
{
    use HasDataTableFeatures, WithExport;

    public string $tableName = 'permission-table';

    public function datasource(): Builder
    {
        $query = Permission::query()
            ->withCount(['roles', 'users'])
            ->select('permissions.*');

        return $this->applyQueryFilters($query);
    }

    public function relationSearch(): array
    {
        return [
            'roles' => ['name'],
            'users' => ['first_name', 'last_name', 'email'],
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

            Column::make('Name', 'name')
                ->sortable()
                ->searchable()
                ->format(function ($value, $row) {
                    $parts = explode('.', $value);
                    $category = ucfirst($parts[0] ?? '');
                    $action = ucfirst($parts[1] ?? '');
                    
                    return '<div class="flex flex-col">
                        <span class="font-medium text-gray-900 dark:text-white">' . $value . '</span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">' . $category . ' → ' . $action . '</span>
                    </div>';
                }),

            Column::make('Category', 'category')
                ->sortable()
                ->format(function ($value, $row) {
                    $category = explode('.', $row->name)[0] ?? 'general';
                    $colors = [
                        'user' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                        'role' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                        'permission' => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
                        'post' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                        'media' => 'bg-pink-100 text-pink-800 dark:bg-pink-900 dark:text-pink-200',
                        'setting' => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200',
                    ];
                    
                    $colorClass = $colors[$category] ?? $colors['setting'];
                    
                    return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ' . $colorClass . '">' . ucfirst($category) . '</span>';
                }),

            Column::make('Guard', 'guard_name')
                ->sortable()
                ->format(function ($value) {
                    return '<span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">' . $value . '</span>';
                }),

            Column::make('Roles Count', 'roles_count')
                ->sortable()
                ->format(function ($value) {
                    return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">' . $value . ' roles</span>';
                }),

            Column::make('Users Count', 'users_count')
                ->sortable()
                ->format(function ($value) {
                    return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">' . $value . ' users</span>';
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
                ->route('admin.permissions.show', fn ($row) => $row->id);
        }

        return $actions;
    }

    protected function getBaseFilters(): array
    {
        // Get unique categories from permission names
        $categories = Permission::get()
            ->map(fn ($permission) => explode('.', $permission->name)[0] ?? 'general')
            ->unique()
            ->sort()
            ->map(fn ($category) => ['name' => ucfirst($category), 'value' => $category])
            ->values()
            ->toArray();

        return [
            Filter::inputText('search')
                ->placeholder('Search permissions...'),

            Filter::select('category')
                ->dataSource($categories)
                ->optionLabel('name')
                ->optionValue('value')
                ->placeholder('Filter by category'),

            Filter::select('guard_name')
                ->dataSource([
                    ['name' => 'Web', 'value' => 'web'],
                    ['name' => 'API', 'value' => 'api'],
                ])
                ->optionLabel('name')
                ->optionValue('value')
                ->placeholder('Filter by guard'),

            Filter::datepicker('created_at')
                ->label('Created Date'),
        ];
    }

    protected function applyBaseQueryFilters($query)
    {
        // Apply search filter
        if ($search = request('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('guard_name', 'like', "%{$search}%");
            });
        }

        // Apply category filter
        if ($category = request('category')) {
            $query->where('name', 'like', "{$category}.%");
        }

        // Apply guard filter
        if ($guard = request('guard_name')) {
            $query->where('guard_name', $guard);
        }

        return $query;
    }

    protected function getHookPrefix(): string
    {
        return 'permission';
    }

    protected function getRouteName(): string
    {
        return 'permissions';
    }

    protected function getModelClass(): string
    {
        return Permission::class;
    }

    protected function getPermissionPrefix(): string
    {
        return 'permission';
    }
}