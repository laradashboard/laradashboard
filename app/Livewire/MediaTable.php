<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Concerns\HasDataTableFeatures;
use App\Models\Media;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\Traits\WithExport;

class MediaTable extends PowerGridComponent
{
    use HasDataTableFeatures, WithExport;

    public string $tableName = 'media-table';
    public string $viewMode = 'table'; // 'table' or 'grid'

    public function datasource(): Builder
    {
        $query = Media::query()
            ->select('media.*');

        return $this->applyQueryFilters($query);
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

            Column::make('Preview', 'id')
                ->bodyAttribute('class', 'w-20')
                ->format(function ($value, $row) {
                    $url = $row->getUrl();
                    $isImage = $this->isImageFile($row->mime_type);
                    
                    if ($isImage) {
                        return '<img src="' . $url . '" alt="' . $row->name . '" class="w-16 h-16 object-cover rounded border">';
                    } else {
                        $icon = $this->getFileIcon($row->mime_type);
                        return '<div class="w-16 h-16 flex items-center justify-center bg-gray-100 dark:bg-gray-700 rounded border">
                            <iconify-icon icon="' . $icon . '" class="text-2xl text-gray-500"></iconify-icon>
                        </div>';
                    }
                }),

            Column::make('Name', 'name')
                ->sortable()
                ->searchable()
                ->format(function ($value, $row) {
                    return '<div class="flex flex-col">
                        <span class="font-medium text-gray-900 dark:text-white">' . \Str::limit($value, 30) . '</span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">' . $row->file_name . '</span>
                    </div>';
                }),

            Column::make('Type', 'mime_type')
                ->sortable()
                ->format(function ($value) {
                    $type = explode('/', $value)[0];
                    $colors = [
                        'image' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                        'video' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                        'audio' => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
                        'application' => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200',
                        'text' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                    ];
                    
                    $colorClass = $colors[$type] ?? $colors['application'];
                    
                    return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ' . $colorClass . '">' . ucfirst($type) . '</span>';
                }),

            Column::make('Size', 'size')
                ->sortable()
                ->format(function ($value) {
                    return '<span class="text-sm text-gray-600 dark:text-gray-300">' . $this->formatFileSize($value) . '</span>';
                }),

            Column::make('Dimensions', 'custom_properties')
                ->format(function ($value, $row) {
                    $properties = $row->custom_properties ?? [];
                    
                    if (isset($properties['width']) && isset($properties['height'])) {
                        return '<span class="text-sm text-gray-600 dark:text-gray-300">' . $properties['width'] . ' × ' . $properties['height'] . '</span>';
                    }
                    
                    return '<span class="text-gray-500 dark:text-gray-400">N/A</span>';
                }),

            Column::make('Collection', 'collection_name')
                ->sortable()
                ->format(function ($value) {
                    return $value ? '<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">' . $value . '</span>' : '<span class="text-gray-500 dark:text-gray-400">Default</span>';
                }),

            $this->formatCreatedAtColumn(),
        ];
    }

    protected function getBaseActions(): array
    {
        $actions = [];

        if ($this->canPerformAction('view')) {
            $actions[] = Button::make('view', 'View')
                ->class('btn-sm btn-info inline-flex items-center px-3 py-1.5 text-xs font-medium text-blue-700 bg-blue-50 border border-blue-300 rounded-md hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-blue-500')
                ->icon('heroicon-o-eye')
                ->method('viewMedia');
        }

        if ($this->canPerformAction('edit')) {
            $actions[] = Button::make('edit', 'Edit')
                ->class('btn-sm btn-secondary inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500')
                ->icon('heroicon-o-pencil')
                ->method('editMedia');
        }

        if ($this->canPerformAction('download')) {
            $actions[] = Button::make('download', 'Download')
                ->class('btn-sm btn-success inline-flex items-center px-3 py-1.5 text-xs font-medium text-green-700 bg-green-50 border border-green-300 rounded-md hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-green-500')
                ->icon('heroicon-o-arrow-down-tray')
                ->method('downloadMedia');
        }

        if ($this->canPerformAction('delete')) {
            $actions[] = Button::make('delete', 'Delete')
                ->class('btn-sm btn-danger inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-700 bg-red-50 border border-red-300 rounded-md hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-500')
                ->icon('heroicon-o-trash')
                ->confirm('Are you sure you want to delete this media file?')
                ->method('deleteMedia');
        }

        return $actions;
    }

    protected function getBaseFilters(): array
    {
        $collections = Media::distinct('collection_name')
            ->pluck('collection_name')
            ->filter()
            ->map(fn ($collection) => ['name' => ucfirst($collection), 'value' => $collection])
            ->values()
            ->toArray();

        $mimeTypes = Media::distinct('mime_type')
            ->pluck('mime_type')
            ->map(function ($mimeType) {
                $type = explode('/', $mimeType)[0];
                return ['name' => ucfirst($type), 'value' => $type];
            })
            ->unique('value')
            ->values()
            ->toArray();

        return [
            Filter::inputText('search')
                ->placeholder('Search media...'),

            Filter::select('mime_type')
                ->dataSource($mimeTypes)
                ->optionLabel('name')
                ->optionValue('value')
                ->placeholder('Filter by type'),

            Filter::select('collection_name')
                ->dataSource($collections)
                ->optionLabel('name')
                ->optionValue('value')
                ->placeholder('Filter by collection'),

            Filter::number('size_min')
                ->label('Min Size (bytes)'),

            Filter::number('size_max')
                ->label('Max Size (bytes)'),

            Filter::datepicker('created_at')
                ->label('Upload Date'),
        ];
    }

    protected function applyBaseQueryFilters($query)
    {
        // Apply search filter
        if ($search = request('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('file_name', 'like', "%{$search}%")
                  ->orWhere('mime_type', 'like', "%{$search}%");
            });
        }

        // Apply mime type filter
        if ($mimeType = request('mime_type')) {
            $query->where('mime_type', 'like', "{$mimeType}/%");
        }

        // Apply collection filter
        if ($collection = request('collection_name')) {
            $query->where('collection_name', $collection);
        }

        // Apply size filters
        if ($sizeMin = request('size_min')) {
            $query->where('size', '>=', $sizeMin);
        }

        if ($sizeMax = request('size_max')) {
            $query->where('size', '<=', $sizeMax);
        }

        return $query;
    }

    protected function getHookPrefix(): string
    {
        return 'media';
    }

    protected function getRouteName(): string
    {
        return 'media';
    }

    protected function getModelClass(): string
    {
        return Media::class;
    }

    protected function getPermissionPrefix(): string
    {
        return 'media';
    }

    /**
     * Toggle view mode between table and grid
     */
    public function toggleViewMode(): void
    {
        $this->viewMode = $this->viewMode === 'table' ? 'grid' : 'table';
        $this->fillData();
    }

    /**
     * View media file
     */
    public function viewMedia(int $id): void
    {
        $media = Media::findOrFail($id);

        $this->dispatchBrowserEvent('show-media-modal', [
            'media' => [
                'id' => $media->id,
                'name' => $media->name,
                'file_name' => $media->file_name,
                'url' => $media->getUrl(),
                'mime_type' => $media->mime_type,
                'size' => $this->formatFileSize($media->size),
                'dimensions' => $this->getMediaDimensions($media),
                'collection' => $media->collection_name,
                'created_at' => $media->created_at->format('M j, Y H:i:s'),
            ]
        ]);
    }

    /**
     * Edit media file
     */
    public function editMedia(int $id): void
    {
        $media = Media::findOrFail($id);

        $this->dispatchBrowserEvent('show-media-edit-modal', [
            'media' => [
                'id' => $media->id,
                'name' => $media->name,
                'file_name' => $media->file_name,
                'collection' => $media->collection_name,
                'custom_properties' => $media->custom_properties ?? [],
            ]
        ]);
    }

    /**
     * Download media file
     */
    public function downloadMedia(int $id): void
    {
        $media = Media::findOrFail($id);
        
        return response()->download($media->getPath(), $media->file_name);
    }

    /**
     * Custom delete method for media
     */
    public function deleteMedia(int $id): void
    {
        $media = Media::findOrFail($id);

        // Apply hooks
        $media = ld_apply_filters('media_delete_before', $media);
        $media->delete();
        ld_do_action('media_delete_after', $media);

        $this->dispatchBrowserEvent('show-message', [
            'type' => 'success',
            'message' => __('Media file deleted successfully.')
        ]);

        $this->fillData();
    }

    /**
     * Helper methods
     */
    private function isImageFile(string $mimeType): bool
    {
        return str_starts_with($mimeType, 'image/');
    }

    private function getFileIcon(string $mimeType): string
    {
        $type = explode('/', $mimeType)[0];
        
        return match ($type) {
            'image' => 'heroicon-o-photo',
            'video' => 'heroicon-o-film',
            'audio' => 'heroicon-o-musical-note',
            'application' => 'heroicon-o-document',
            'text' => 'heroicon-o-document-text',
            default => 'heroicon-o-document',
        };
    }

    private function formatFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    private function getMediaDimensions(Media $media): ?string
    {
        $properties = $media->custom_properties ?? [];
        
        if (isset($properties['width']) && isset($properties['height'])) {
            return $properties['width'] . ' × ' . $properties['height'];
        }
        
        return null;
    }
}