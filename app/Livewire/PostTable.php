<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Concerns\HasDataTableFeatures;
use App\Models\Post;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\Traits\WithExport;

class PostTable extends PowerGridComponent
{
    use HasDataTableFeatures, WithExport;

    public string $tableName = 'post-table';

    public function datasource(): Builder
    {
        $query = Post::query()
            ->with(['author', 'featuredImage'])
            ->withCount(['terms'])
            ->select('posts.*');

        return $this->applyQueryFilters($query);
    }

    public function relationSearch(): array
    {
        return [
            'author' => ['first_name', 'last_name', 'email'],
            'terms' => ['name'],
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

            Column::make('Featured Image', 'featured_image_id')
                ->bodyAttribute('class', 'w-20')
                ->format(function ($value, $row) {
                    $imageUrl = $row->featuredImage?->getUrl() ?? '/images/default.svg';
                    return '<img src="' . $imageUrl . '" alt="' . $row->title . '" class="w-16 h-12 object-cover rounded">';
                }),

            Column::make('Title', 'title')
                ->sortable()
                ->searchable()
                ->format(function ($value, $row) {
                    $statusColors = [
                        'published' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                        'draft' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                        'private' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                        'trash' => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200',
                    ];
                    
                    $statusColor = $statusColors[$row->status] ?? $statusColors['draft'];
                    
                    return '<div class="flex flex-col">
                        <span class="font-medium text-gray-900 dark:text-white">' . \Str::limit($value, 40) . '</span>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium ' . $statusColor . '">' . ucfirst($row->status) . '</span>
                            <span class="text-xs text-gray-500 dark:text-gray-400">' . ucfirst($row->type) . '</span>
                        </div>
                    </div>';
                }),

            Column::make('Author', 'author_id')
                ->sortable()
                ->searchable()
                ->format(function ($value, $row) {
                    if (!$row->author) {
                        return '<span class="text-gray-500 dark:text-gray-400">No Author</span>';
                    }
                    
                    return '<div class="flex items-center">
                        <img src="' . $row->author->avatar_url . '" alt="' . $row->author->full_name . '" class="w-6 h-6 rounded-full mr-2">
                        <span class="text-sm text-gray-900 dark:text-white">' . $row->author->full_name . '</span>
                    </div>';
                }),

            Column::make('Categories/Tags', 'terms_count')
                ->sortable()
                ->format(function ($value, $row) {
                    if ($value == 0) {
                        return '<span class="text-gray-500 dark:text-gray-400">No terms</span>';
                    }
                    
                    return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">' . $value . ' terms</span>';
                }),

            Column::make('Views', 'view_count')
                ->sortable()
                ->format(function ($value) {
                    return '<span class="text-sm text-gray-600 dark:text-gray-300">' . number_format($value ?? 0) . '</span>';
                }),

            $this->formatCreatedAtColumn(),

            Column::make('Published', 'published_at')
                ->sortable()
                ->format(function ($value) {
                    return $value ? '<span class="text-sm text-gray-600 dark:text-gray-300">' . $value->format('M j, Y') . '</span>' : '<span class="text-gray-500 dark:text-gray-400">Not published</span>';
                }),
        ];
    }

    protected function getBaseActions(): array
    {
        $actions = [];

        if ($this->canPerformAction('view')) {
            $actions[] = Button::make('view', 'View')
                ->class('btn-sm btn-info inline-flex items-center px-3 py-1.5 text-xs font-medium text-blue-700 bg-blue-50 border border-blue-300 rounded-md hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-blue-500')
                ->icon('heroicon-o-eye')
                ->route('admin.posts.show', fn ($row) => $row->id);
        }

        if ($this->canPerformAction('edit')) {
            $actions[] = Button::make('edit', 'Edit')
                ->class('btn-sm btn-secondary inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500')
                ->icon('heroicon-o-pencil')
                ->route('admin.posts.edit', fn ($row) => $row->id);
        }

        if ($this->canPerformAction('delete')) {
            $actions[] = Button::make('delete', 'Delete')
                ->class('btn-sm btn-danger inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-700 bg-red-50 border border-red-300 rounded-md hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-500')
                ->icon('heroicon-o-trash')
                ->confirm('Are you sure you want to delete this post?')
                ->method('deletePost');
        }

        return $actions;
    }

    protected function getBaseFilters(): array
    {
        $statuses = [
            ['name' => 'Published', 'value' => 'published'],
            ['name' => 'Draft', 'value' => 'draft'],
            ['name' => 'Private', 'value' => 'private'],
            ['name' => 'Trash', 'value' => 'trash'],
        ];

        $types = [
            ['name' => 'Post', 'value' => 'post'],
            ['name' => 'Page', 'value' => 'page'],
        ];

        $authors = \App\Models\User::select('id', 'first_name', 'last_name')
            ->get()
            ->map(fn ($user) => ['name' => $user->full_name, 'value' => $user->id])
            ->toArray();

        return [
            Filter::inputText('search')
                ->placeholder('Search posts...'),

            Filter::select('status')
                ->dataSource($statuses)
                ->optionLabel('name')
                ->optionValue('value')
                ->placeholder('Filter by status'),

            Filter::select('type')
                ->dataSource($types)
                ->optionLabel('name')
                ->optionValue('value')
                ->placeholder('Filter by type'),

            Filter::select('author_id')
                ->dataSource($authors)
                ->optionLabel('name')
                ->optionValue('value')
                ->placeholder('Filter by author'),

            Filter::datepicker('created_at')
                ->label('Created Date'),

            Filter::datepicker('published_at')
                ->label('Published Date'),
        ];
    }

    protected function applyBaseQueryFilters($query)
    {
        // Apply search filter
        if ($search = request('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('excerpt', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        // Apply status filter
        if ($status = request('status')) {
            $query->where('status', $status);
        }

        // Apply type filter
        if ($type = request('type')) {
            $query->where('type', $type);
        }

        // Apply author filter
        if ($authorId = request('author_id')) {
            $query->where('author_id', $authorId);
        }

        return $query;
    }

    protected function getHookPrefix(): string
    {
        return 'post';
    }

    protected function getRouteName(): string
    {
        return 'posts';
    }

    protected function getModelClass(): string
    {
        return Post::class;
    }

    protected function getPermissionPrefix(): string
    {
        return 'post';
    }

    /**
     * Custom delete method for posts
     */
    public function deletePost(int $id): void
    {
        $post = Post::findOrFail($id);

        // Apply hooks
        $post = ld_apply_filters('post_delete_before', $post);
        $post->delete();
        ld_do_action('post_delete_after', $post);

        $this->dispatchBrowserEvent('show-message', [
            'type' => 'success',
            'message' => __('Post deleted successfully.')
        ]);

        $this->fillData();
    }
}