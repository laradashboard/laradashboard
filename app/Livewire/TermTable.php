<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Concerns\HasDataTableFeatures;
use App\Models\Term;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\Traits\WithExport;

class TermTable extends PowerGridComponent
{
    use HasDataTableFeatures, WithExport;

    public string $tableName = 'term-table';

    public function datasource(): Builder
    {
        $query = Term::query()
            ->with(['taxonomy', 'parent'])
            ->withCount(['posts'])
            ->select('terms.*');

        return $this->applyQueryFilters($query);
    }

    public function relationSearch(): array
    {
        return [
            'taxonomy' => ['name'],
            'parent' => ['name'],
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
                    $indent = $row->parent_id ? '— ' : '';
                    
                    return '<div class="flex flex-col">
                        <span class="font-medium text-gray-900 dark:text-white">' . $indent . $value . '</span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">' . $row->slug . '</span>
                    </div>';
                }),

            Column::make('Taxonomy', 'taxonomy_id')
                ->sortable()
                ->searchable()
                ->format(function ($value, $row) {
                    if (!$row->taxonomy) {
                        return '<span class="text-gray-500 dark:text-gray-400">No taxonomy</span>';
                    }
                    
                    $colors = [
                        'category' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                        'tag' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                        'post_tag' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                        'post_category' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                    ];
                    
                    $colorClass = $colors[$row->taxonomy->name] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200';
                    
                    return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ' . $colorClass . '">' . ucfirst($row->taxonomy->name) . '</span>';
                }),

            Column::make('Parent', 'parent_id')
                ->sortable()
                ->format(function ($value, $row) {
                    if (!$row->parent) {
                        return '<span class="text-gray-500 dark:text-gray-400">None</span>';
                    }
                    
                    return '<span class="text-sm text-gray-600 dark:text-gray-300">' . $row->parent->name . '</span>';
                }),

            Column::make('Description', 'description')
                ->format(function ($value) {
                    return $value ? '<span class="text-sm text-gray-600 dark:text-gray-300">' . \Str::limit($value, 50) . '</span>' : '<span class="text-gray-500 dark:text-gray-400">No description</span>';
                }),

            Column::make('Posts Count', 'posts_count')
                ->sortable()
                ->format(function ($value) {
                    return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">' . $value . ' posts</span>';
                }),

            Column::make('Order', 'sort_order')
                ->sortable()
                ->format(function ($value) {
                    return '<span class="text-sm text-gray-600 dark:text-gray-300">' . ($value ?? 0) . '</span>';
                }),

            $this->formatCreatedAtColumn(),
        ];
    }

    protected function getBaseActions(): array
    {
        $actions = [];

        if ($this->canPerformAction('edit')) {
            $actions[] = Button::make('edit', 'Edit')
                ->class('btn-sm btn-secondary inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500')
                ->icon('heroicon-o-pencil')
                ->route('admin.terms.edit', fn ($row) => $row->id);
        }

        if ($this->canPerformAction('delete')) {
            $actions[] = Button::make('delete', 'Delete')
                ->class('btn-sm btn-danger inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-700 bg-red-50 border border-red-300 rounded-md hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-500')
                ->icon('heroicon-o-trash')
                ->confirm('Are you sure you want to delete this term? This will also remove it from all associated posts.')
                ->method('deleteTerm');
        }

        return $actions;
    }

    protected function getBaseFilters(): array
    {
        $taxonomies = \App\Models\Taxonomy::select('id', 'name')
            ->get()
            ->map(fn ($taxonomy) => ['name' => ucfirst($taxonomy->name), 'value' => $taxonomy->id])
            ->toArray();

        $parentTerms = Term::whereNull('parent_id')
            ->select('id', 'name')
            ->get()
            ->map(fn ($term) => ['name' => $term->name, 'value' => $term->id])
            ->toArray();

        return [
            Filter::inputText('search')
                ->placeholder('Search terms...'),

            Filter::select('taxonomy_id')
                ->dataSource($taxonomies)
                ->optionLabel('name')
                ->optionValue('value')
                ->placeholder('Filter by taxonomy'),

            Filter::select('parent_id')
                ->dataSource($parentTerms)
                ->optionLabel('name')
                ->optionValue('value')
                ->placeholder('Filter by parent'),

            Filter::boolean('has_parent')
                ->label('Has Parent'),

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
                  ->orWhere('slug', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Apply taxonomy filter
        if ($taxonomyId = request('taxonomy_id')) {
            $query->where('taxonomy_id', $taxonomyId);
        }

        // Apply parent filter
        if ($parentId = request('parent_id')) {
            $query->where('parent_id', $parentId);
        }

        // Apply has parent filter
        if (request('has_parent') !== null) {
            if (request('has_parent')) {
                $query->whereNotNull('parent_id');
            } else {
                $query->whereNull('parent_id');
            }
        }

        return $query;
    }

    protected function getHookPrefix(): string
    {
        return 'term';
    }

    protected function getRouteName(): string
    {
        return 'terms';
    }

    protected function getModelClass(): string
    {
        return Term::class;
    }

    protected function getPermissionPrefix(): string
    {
        return 'term';
    }

    /**
     * Custom delete method for terms
     */
    public function deleteTerm(int $id): void
    {
        $term = Term::with(['children', 'posts'])->findOrFail($id);

        // Check if term has children
        if ($term->children()->count() > 0) {
            $this->dispatchBrowserEvent('show-message', [
                'type' => 'error',
                'message' => __('Cannot delete term that has child terms. Please delete or reassign child terms first.')
            ]);
            return;
        }

        // Apply hooks
        $term = ld_apply_filters('term_delete_before', $term);
        
        // Detach from posts before deleting
        $term->posts()->detach();
        
        $term->delete();
        ld_do_action('term_delete_after', $term);

        $this->dispatchBrowserEvent('show-message', [
            'type' => 'success',
            'message' => __('Term deleted successfully.')
        ]);

        $this->fillData();
    }
}