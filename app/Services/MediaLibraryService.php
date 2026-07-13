<?php

declare(strict_types=1);

namespace App\Services;

use App\Concerns\HandlesMediaOperations;
use App\Models\Media;
use App\Models\Post;
use App\Services\Builder\PostBuilderService;
use App\Support\Helper\MediaHelper;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media as SpatieMedia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MediaLibraryService
{
    use HandlesMediaOperations;

    public function getMediaList(
        ?string $search = null,
        ?string $type = null,
        string $sort = 'created_at',
        string $direction = 'desc',
        int $perPage = 24
    ): array {
        $query = SpatieMedia::query()->latest();

        // Apply search filter
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('file_name', 'like', "%{$search}%")
                    ->orWhere('mime_type', 'like', "%{$search}%");
            });
        }

        // Apply type filter
        if ($type) {
            switch ($type) {
                case 'images':
                    $query->where('mime_type', 'like', 'image/%');
                    break;
                case 'videos':
                    $query->where('mime_type', 'like', 'video/%');
                    break;
                case 'audio':
                    $query->where('mime_type', 'like', 'audio/%');
                    break;
                case 'documents':
                    $query->whereNotIn('mime_type', function ($q) {
                        $q->select('mime_type')
                            ->from('media')
                            ->where('mime_type', 'like', 'image/%')
                            ->orWhere('mime_type', 'like', 'video/%')
                            ->orWhere('mime_type', 'like', 'audio/%');
                    });
                    break;
            }
        }

        // Apply sorting
        if (in_array($sort, ['name', 'size', 'created_at', 'mime_type'])) {
            $query->orderBy($sort, $direction);
        }

        // Paginate results
        $media = $query->paginate($perPage)->withQueryString();

        // Enhance media items with additional information
        // Note: human_readable_size is already provided by Spatie's Media model
        $media->getCollection()->transform(function ($item) {
            $item->setAttribute('file_type_category', $this->getFileTypeCategory($item->mime_type));
            $item->setAttribute('media_icon', $this->getMediaIcon($item->mime_type));

            return $item;
        });

        // Get statistics
        $stats = $this->getMediaStatistics();

        return [
            'media' => $media,
            'stats' => $stats,
        ];
    }

    public function getMediaStatistics(): array
    {
        return [
            'total' => SpatieMedia::count(),
            'images' => SpatieMedia::where('mime_type', 'like', 'image/%')->count(),
            'videos' => SpatieMedia::where('mime_type', 'like', 'video/%')->count(),
            'audio' => SpatieMedia::where('mime_type', 'like', 'audio/%')->count(),
            'documents' => SpatieMedia::whereNotLike('mime_type', 'image/%')
                ->whereNotLike('mime_type', 'video/%')
                ->whereNotLike('mime_type', 'audio/%')
                ->count(),
            'total_size' => $this->formatFileSize((int) SpatieMedia::sum('size')),
        ];
    }

    public function uploadMedia(array $files): array
    {
        $uploadedFiles = [];

        foreach ($files as $file) {
            // Skip files that don't pass security checks
            if (! $this->isSecureFile($file)) {
                continue;
            }

            // Check demo mode restrictions.
            if (config('app.demo_mode', false)) {
                $mimeType = $file->getMimeType();
                if (! MediaHelper::isAllowedInDemoMode($mimeType)) {
                    throw new \InvalidArgumentException(__('In demo mode, only images, videos, PDFs, and documents are allowed. File type :type is not permitted.', ['type' => $mimeType]));
                }
            }

            // Generate a secure filename
            $safeFileName = $this->generateUniqueFilename($file->getClientOriginalName());

            // Store the file with a secure name
            $path = $file->storeAs('media', $safeFileName, 'public');

            // Create media record directly in the media table for standalone uploads
            $mediaItem = SpatieMedia::create([
                'model_type' => '', // Empty for standalone media
                'model_id' => 0,   // 0 for standalone media
                'uuid' => Str::uuid(),
                'collection_name' => 'uploads',
                'name' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                'file_name' => basename($path),
                'mime_type' => $file->getMimeType(),
                'disk' => 'public',
                'conversions_disk' => 'public',
                'size' => $file->getSize(),
                'manipulations' => [],
                'custom_properties' => [],
                'generated_conversions' => [],
                'responsive_images' => [],
                'order_column' => null,
            ]);

            $uploadedFiles[] = $mediaItem;
        }

        return $uploadedFiles;
    }

    public function deleteMedia(int $id): bool
    {
        $media = Media::findOrFail($id);

        // Delete the physical file - construct path manually to avoid Spatie method issues
        $filePath = 'media/' . $media->file_name;

        if (Storage::disk($media->disk)->exists($filePath)) {
            Storage::disk($media->disk)->delete($filePath);
        }

        return $media->delete();
    }

    public function bulkDeleteMedia(array $ids): int
    {
        $deleteCount = 0;
        $media = Media::whereIn('id', $ids)->get();

        foreach ($media as $item) {
            // Delete the physical file - construct path manually to avoid Spatie method issues
            $filePath = 'media/' . $item->file_name;

            if (Storage::disk($item->disk)->exists($filePath)) {
                Storage::disk($item->disk)->delete($filePath);
            }

            if ($item->delete()) {
                $deleteCount++;
            }
        }

        return $deleteCount;
    }

    public function uploadFromRequest(
        HasMedia $model,
        Request $request,
        string $fieldName,
        string $collection = 'default'
    ): ?SpatieMedia {
        if ($request->hasFile($fieldName)) {
            $file = $request->file($fieldName);

            // Security checks
            if ($file && $this->isSecureFile($file)) {
                // Check demo mode restrictions
                if (config('app.demo_mode', false)) {
                    $mimeType = $file->getMimeType();
                    if (! MediaHelper::isAllowedInDemoMode($mimeType)) {
                        throw new \InvalidArgumentException(__('In demo mode, only images, videos, PDFs, and documents are allowed. File type :type is not permitted.', ['type' => $mimeType]));
                    }
                }

                return $model->addMedia($file)
                    ->sanitizingFileName(function ($fileName) {
                        return $this->sanitizeFilename($fileName);
                    })
                    ->toMediaCollection($collection);
            }
        }

        return null;
    }

    public function uploadMultipleFromRequest(
        HasMedia $model,
        Request $request,
        string $requestKey,
        string $collection = 'default'
    ): void {
        if ($request->hasFile($requestKey)) {
            foreach ($request->file($requestKey) as $file) {
                // Security checks
                if ($this->isSecureFile($file)) {
                    // Check demo mode restrictions
                    if (config('app.demo_mode', false)) {
                        $mimeType = $file->getMimeType();
                        if (! MediaHelper::isAllowedInDemoMode($mimeType)) {
                            throw new \InvalidArgumentException(__('In demo mode, only images, videos, PDFs, and documents are allowed. File type :type is not permitted.', ['type' => $mimeType]));
                        }
                    }

                    $model->addMedia($file)
                        ->sanitizingFileName(function ($fileName) {
                            return $this->sanitizeFilename($fileName);
                        })
                        ->toMediaCollection($collection);
                }
            }
        }
    }

    /**
     * Link a post to a library media item without moving or copying the file.
     */
    public function setPostFeaturedMedia(Post $post, SpatieMedia $media): SpatieMedia
    {
        $this->normalizeMediaAttributes($media);
        $this->releaseMediaCollection($post, 'featured');

        if (! $this->isStandaloneMedia($media)) {
            $this->releaseMediaToLibrary($media);
        }

        $post->setMeta(PostBuilderService::META_FEATURED_MEDIA_ID, (string) $media->id);

        return $media->fresh();
    }

    public function clearPostFeaturedMedia(Post $post): void
    {
        $this->releaseMediaCollection($post, 'featured');
        $post->deleteMeta(PostBuilderService::META_FEATURED_MEDIA_ID);
    }

    public function clearMediaCollection(HasMedia $model, string $collection = 'default'): void
    {
        $this->releaseMediaCollection($model, $collection);
    }

    /**
     * Detach media from a model and return it to the standalone library.
     */
    public function releaseMediaCollection(HasMedia $model, string $collection = 'default'): void
    {
        $model->getMedia($collection)->each(function (SpatieMedia $media): void {
            $this->releaseMediaToLibrary($media);
        });
    }

    /**
     * Associate existing media with a model by URL or ID.
     *
     * Reuses library uploads by reassigning the existing media record instead of
     * copying the file, which previously created duplicate library entries.
     */
    public function associateExistingMedia(
        HasMedia $model,
        string $mediaUrlOrId,
        string $collection = 'default'
    ): ?SpatieMedia {
        $media = $this->findMediaByUrlOrId($mediaUrlOrId);

        if (! $media) {
            Log::warning("Media not found for ID/URL: {$mediaUrlOrId}");

            return null;
        }

        if ($this->isMediaAlreadyAssociated($model, $media, $collection)) {
            if ($collection === 'featured' && $model instanceof Post) {
                return $this->setPostFeaturedMedia($model, $media);
            }

            return $media;
        }

        $this->normalizeMediaAttributes($media);

        if ($collection === 'featured' && $model instanceof Post) {
            return $this->setPostFeaturedMedia($model, $media);
        }

        $this->clearMediaCollectionExcept($model, $collection, (int) $media->id);

        if ($this->canReassignMedia($media, $model)) {
            return $this->reassignMediaToModel($model, $media, $collection);
        }

        return $this->copyMediaToModel($model, $media, $collection);
    }

    protected function findMediaByUrlOrId(string $mediaUrlOrId): ?SpatieMedia
    {
        if (is_numeric($mediaUrlOrId)) {
            return SpatieMedia::find((int) $mediaUrlOrId);
        }

        $urlPath = parse_url($mediaUrlOrId, PHP_URL_PATH);
        $fileName = basename((string) $urlPath);

        $media = SpatieMedia::where('file_name', $fileName)->first();

        if ($media) {
            return $media;
        }

        return SpatieMedia::where('disk', 'public')
            ->get()
            ->first(function ($item) use ($mediaUrlOrId) {
                try {
                    return $item->getUrl() === $mediaUrlOrId;
                } catch (\Throwable $e) {
                    return false;
                }
            });
    }

    protected function isMediaAlreadyAssociated(
        HasMedia $model,
        SpatieMedia $media,
        string $collection
    ): bool {
        if ($media->model_type === $model->getMorphClass()
            && (int) $media->model_id === (int) $model->getKey()
            && $media->collection_name === $collection) {
            return true;
        }

        $currentMedia = $model->getMedia($collection)->first();

        return $currentMedia instanceof SpatieMedia
            && (int) $currentMedia->id === (int) $media->id;
    }

    protected function isStandaloneMedia(SpatieMedia $media): bool
    {
        return (int) $media->model_id === 0 || $media->model_type === '' || $media->model_type === null;
    }

    protected function canReassignMedia(SpatieMedia $media, HasMedia $model): bool
    {
        if ($this->isStandaloneMedia($media)) {
            return true;
        }

        return $media->model_type === $model->getMorphClass()
            && (int) $media->model_id === (int) $model->getKey();
    }

    protected function clearMediaCollectionExcept(
        HasMedia $model,
        string $collection,
        int $exceptMediaId
    ): void {
        $model->getMedia($collection)->each(function (SpatieMedia $existingMedia) use ($exceptMediaId): void {
            if ((int) $existingMedia->id === $exceptMediaId) {
                return;
            }

            $this->releaseMediaToLibrary($existingMedia);
        });
    }

    protected function releaseMediaToLibrary(SpatieMedia $media): void
    {
        if ($this->isStandaloneMedia($media)) {
            return;
        }

        $this->normalizeMediaAttributes($media);

        $sourcePath = $this->resolveMediaPath($media);
        $libraryDirectory = storage_path('app/public/media');
        $libraryPath = $libraryDirectory . '/' . $media->file_name;

        if ($sourcePath !== null && file_exists($sourcePath) && $sourcePath !== $libraryPath) {
            if (! is_dir($libraryDirectory)) {
                mkdir($libraryDirectory, 0755, true);
            }

            if (! file_exists($libraryPath)) {
                rename($sourcePath, $libraryPath);
            }
        }

        $media->model_type = '';
        $media->model_id = 0;
        $media->collection_name = 'uploads';
        $media->order_column = null;
        $media->save();
    }

    protected function deleteMediaRecordSafely(SpatieMedia $media): void
    {
        $this->normalizeMediaAttributes($media);

        try {
            $media->delete();
        } catch (\Throwable $e) {
            Log::warning('Failed to delete media via Spatie, using manual cleanup', [
                'media_id' => $media->id,
                'error' => $e->getMessage(),
            ]);

            $this->deleteMediaFilesFromDisk($media);
            SpatieMedia::query()->whereKey($media->id)->delete();
        }
    }

    /**
     * Legacy uploads stored JSON columns as strings, which breaks Spatie deletes.
     */
    protected function normalizeMediaAttributes(SpatieMedia $media): SpatieMedia
    {
        foreach (['manipulations', 'custom_properties', 'generated_conversions', 'responsive_images'] as $attribute) {
            $value = $media->getAttributes()[$attribute] ?? null;

            if (is_string($value)) {
                $decoded = json_decode($value, true);
                $media->setAttribute($attribute, is_array($decoded) ? $decoded : []);
            } elseif ($value === null) {
                $media->setAttribute($attribute, []);
            }
        }

        return $media;
    }

    protected function deleteMediaFilesFromDisk(SpatieMedia $media): void
    {
        $paths = array_filter([
            $this->resolveMediaPath($media),
        ]);

        try {
            $paths[] = $media->getPath();
        } catch (\Throwable $e) {
            // Ignore path resolution failures for legacy rows.
        }

        foreach (array_unique($paths) as $path) {
            if (is_string($path) && file_exists($path)) {
                @unlink($path);
            }
        }
    }

    protected function reassignMediaToModel(
        HasMedia $model,
        SpatieMedia $media,
        string $collection
    ): SpatieMedia {
        $sourcePath = $this->resolveMediaPath($media);

        $media->model_type = $model->getMorphClass();
        $media->model_id = $model->getKey();
        $media->collection_name = $collection;
        $media->order_column = $media->order_column ?? 1;
        $this->normalizeMediaAttributes($media);
        $media->save();

        $media = $media->fresh();

        if ($sourcePath !== null && file_exists($sourcePath)) {
            $destinationPath = $media->getPath();

            if ($sourcePath !== $destinationPath) {
                $destinationDirectory = dirname($destinationPath);

                if (! is_dir($destinationDirectory)) {
                    mkdir($destinationDirectory, 0755, true);
                }

                rename($sourcePath, $destinationPath);
            }
        }

        return $media;
    }

    /**
     * Resolve public URLs for a media item (original + thumbnail).
     *
     * @return array{url: string, thumbnail_url: string}
     */
    public function resolveMediaUrls(SpatieMedia $media): array
    {
        $url = $this->resolveMediaUrl($media) ?? asset('storage/media/' . $media->file_name);
        $thumbnailUrl = $this->resolveMediaUrl($media, 'thumb') ?? $url;

        return [
            'url' => $url,
            'thumbnail_url' => $thumbnailUrl,
        ];
    }

    /**
     * Resolve a public URL for a media item, including legacy standalone paths.
     */
    public function resolveMediaUrl(SpatieMedia $media, string $conversion = ''): ?string
    {
        if ($conversion !== '') {
            try {
                if ($media->hasGeneratedConversion($conversion)) {
                    $conversionPath = $media->getPath($conversion);

                    if (file_exists($conversionPath)) {
                        return $media->getUrl($conversion);
                    }
                }
            } catch (\Throwable $e) {
                // Legacy media rows may store JSON fields as strings.
            }

            return $this->resolveOriginalMediaUrl($media);
        }

        return $this->resolveOriginalMediaUrl($media);
    }

    protected function resolveOriginalMediaUrl(SpatieMedia $media): ?string
    {
        $resolvedPath = $this->resolveMediaPath($media);

        if ($resolvedPath !== null && file_exists($resolvedPath)) {
            if (! $this->isStandaloneMedia($media) && str_contains($resolvedPath, '/media/' . $media->file_name)) {
                $this->moveMediaFileToExpectedPath($media, $resolvedPath);

                try {
                    return $media->getUrl();
                } catch (\Throwable $e) {
                    return asset('storage/media/' . $media->file_name);
                }
            }

            if ($this->isStandaloneMedia($media) || str_contains($resolvedPath, '/media/' . $media->file_name)) {
                return asset('storage/media/' . $media->file_name);
            }

            try {
                return $media->getUrl();
            } catch (\Throwable $e) {
                return asset('storage/media/' . $media->file_name);
            }
        }

        try {
            return $media->getUrl();
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected function moveMediaFileToExpectedPath(SpatieMedia $media, string $sourcePath): void
    {
        try {
            $destinationPath = $media->getPath();
        } catch (\Throwable $e) {
            return;
        }

        if ($sourcePath === $destinationPath || ! file_exists($sourcePath)) {
            return;
        }

        $destinationDirectory = dirname($destinationPath);

        if (! is_dir($destinationDirectory)) {
            mkdir($destinationDirectory, 0755, true);
        }

        rename($sourcePath, $destinationPath);
    }

    protected function copyMediaToModel(
        HasMedia $model,
        SpatieMedia $media,
        string $collection
    ): ?SpatieMedia {
        try {
            $mediaPath = $this->resolveMediaPath($media);

            if ($mediaPath !== null && file_exists($mediaPath)) {
                return $model
                    ->addMedia($mediaPath)
                    ->preservingOriginal()
                    ->usingName($media->name)
                    ->usingFileName($media->file_name)
                    ->toMediaCollection($collection);
            }

            Log::warning('Media file does not exist at any expected path', [
                'media_id' => $media->id,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to associate existing media: ' . $e->getMessage(), [
                'media_id' => $media->id,
                'exception' => $e,
            ]);
        }

        return null;
    }

    protected function resolveMediaPath(SpatieMedia $media): ?string
    {
        if ($this->isStandaloneMedia($media)) {
            $primaryPath = storage_path('app/public/media/' . $media->file_name);

            if (file_exists($primaryPath)) {
                return $primaryPath;
            }
        } else {
            $modelPath = $media->getPath();

            if (file_exists($modelPath)) {
                return $modelPath;
            }
        }

        $alternativePaths = [
            storage_path('app/public/media/' . $media->file_name),
            storage_path('app/public/' . $media->file_name),
            storage_path('app/public/uploads/' . $media->file_name),
            public_path('storage/media/' . $media->file_name),
            public_path('storage/' . $media->file_name),
        ];

        foreach ($alternativePaths as $alternativePath) {
            if (file_exists($alternativePath)) {
                return $alternativePath;
            }
        }

        return null;
    }
}
