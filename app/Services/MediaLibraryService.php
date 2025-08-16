<?php

declare(strict_types=1);

namespace App\Services;

use App\Concerns\HandlesMediaOperations;
use App\Models\Media;
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
        $media->getCollection()->transform(function ($item) {
            $item->human_readable_size = $this->formatFileSize($item->size);
            $item->file_type_category = $this->getFileTypeCategory($item->mime_type);
            $item->icon = $this->getMediaIcon($item->mime_type);
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
                'manipulations' => '[]',
                'custom_properties' => '[]',
                'generated_conversions' => '[]',
                'responsive_images' => '[]',
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

    public function clearMediaCollection(HasMedia $model, string $collection = 'default'): void
    {
        $model->clearMediaCollection($collection);
    }

    /**
     * Associate existing media with a model by URL or ID
     */
    public function associateExistingMedia(
        HasMedia $model,
        string $mediaUrlOrId,
        string $collection = 'default'
    ): ?SpatieMedia {
        $media = null;

        // Try to find media by ID first (most reliable)
        if (is_numeric($mediaUrlOrId)) {
            $media = SpatieMedia::find($mediaUrlOrId);
        } else {
            // Extract file name from URL path
            $urlPath = parse_url($mediaUrlOrId, PHP_URL_PATH);
            $fileName = basename($urlPath);

            // Try to find media by file name
            $media = SpatieMedia::where('file_name', $fileName)->first();

            // If not found, try by full URL pattern
            if (! $media) {
                $media = SpatieMedia::where('disk', 'public')
                    ->get()
                    ->first(function ($item) use ($mediaUrlOrId) {
                        try {
                            return $item->getUrl() === $mediaUrlOrId;
                        } catch (\Exception $e) {
                            return false;
                        }
                    });
            }
        }

        if (! $media) {
            Log::warning("Media not found for ID/URL: {$mediaUrlOrId}");
            return null;
        }

        // Copy the media file to associate it with the model
        try {
            // For standalone media (model_id = 0), construct the correct path
            if ($media->model_id == 0) {
                // Standalone media is stored in media/ directory
                $mediaPath = storage_path('app/public/media/' . $media->file_name);
            } else {
                // Model-attached media uses the default path
                $mediaPath = $media->getPath();
            }

            if (file_exists($mediaPath)) {
                $copiedMedia = $model
                    ->addMedia($mediaPath)
                    ->preservingOriginal()
                    ->usingName($media->name)
                    ->usingFileName($media->file_name)
                    ->toMediaCollection($collection);

                return $copiedMedia;
            } else {
                // Try alternative paths for different storage structures
                $alternativePaths = [
                    storage_path('app/public/' . $media->file_name),
                    storage_path('app/public/uploads/' . $media->file_name),
                    public_path('storage/media/' . $media->file_name),
                    public_path('storage/' . $media->file_name),
                ];

                foreach ($alternativePaths as $altPath) {
                    if (file_exists($altPath)) {
                        $copiedMedia = $model
                            ->addMedia($altPath)
                            ->preservingOriginal()
                            ->usingName($media->name)
                            ->usingFileName($media->file_name)
                            ->toMediaCollection($collection);
                        return $copiedMedia;
                    }
                }

                Log::warning("Media file does not exist at any expected path", [
                    'primary_path' => $mediaPath,
                    'alternative_paths' => $alternativePaths,
                    'media_id' => $media->id,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to associate existing media: ' . $e->getMessage(), [
                'media_id' => $media->id,
                'exception' => $e,
            ]);
        }

        return null;
    }
}
