<?php

declare(strict_types=1);

namespace App\Services;

use App\Contacts\MediaInterface;
use App\Helper\MediaHelper;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\MediaLibrary\MediaCollections\Models\Media as SpatieMedia;
use Illuminate\Support\Facades\Storage;

class MediaLibraryService
{

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
                case 'documents':
                    $query->whereNotIn('mime_type', function ($q) {
                        $q->select('mime_type')
                            ->from('media')
                            ->where('mime_type', 'like', 'image/%')
                            ->orWhere('mime_type', 'like', 'video/%');
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
            $item->human_readable_size = MediaHelper::formatFileSize($item->size);
            $item->file_type_category = MediaHelper::getFileTypeCategory($item->mime_type);
            $item->icon = MediaHelper::getMediaIcon($item->mime_type);
            return $item;
        });

        // Get statistics
        $stats = $this->getMediaStatistics();

        return [
            'media' => $media,
            'stats' => $stats
        ];
    }

    public function getMediaStatistics(): array
    {
        return [
            'total' => SpatieMedia::count(),
            'images' => SpatieMedia::where('mime_type', 'like', 'image/%')->count(),
            'videos' => SpatieMedia::where('mime_type', 'like', 'video/%')->count(),
            'documents' => SpatieMedia::whereNotLike('mime_type', 'image/%')
                ->whereNotLike('mime_type', 'video/%')
                ->count(),
            'total_size' => MediaHelper::formatFileSize((int) SpatieMedia::sum('size')),
        ];
    }

    public function uploadMedia(array $files): array
    {
        $uploadedFiles = [];

        foreach ($files as $file) {
            // Skip files that don't pass security checks
            if (!$this->isSecureFile($file)) {
                continue;
            }

            // Generate a secure filename
            $safeFileName = MediaHelper::generateUniqueFilename($file->getClientOriginalName());
            
            // Store the file with a secure name
            $path = $file->storeAs('media', $safeFileName, 'public');

            // Create media record
            $mediaItem = SpatieMedia::create([
                'model_type' => SpatieMedia::class,
                'model_id' => 0,
                'uuid' => null,
                'collection_name' => 'default',
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
        $media = SpatieMedia::findOrFail($id);

        // Delete the physical file
        if (Storage::disk($media->disk)->exists($media->getPath())) {
            Storage::disk($media->disk)->delete($media->getPath());
        }

        return $media->delete();
    }

    public function bulkDeleteMedia(array $ids): int
    {
        $deleteCount = 0;
        $media = SpatieMedia::whereIn('id', $ids)->get();

        foreach ($media as $item) {
            if (Storage::disk($item->disk)->exists($item->getPath())) {
                Storage::disk($item->disk)->delete($item->getPath());
            }
            if ($item->delete()) {
                $deleteCount++;
            }
        }

        return $deleteCount;
    }

    public function uploadFromRequest(
        MediaInterface $model,
        Request $request,
        string $requestKey,
        string $collection = 'default'
    ): void {
        if ($request->hasFile($requestKey)) {
            $file = $request->file($requestKey);
            
            // Security checks
            if ($this->isSecureFile($file)) {
                $model->addMedia($file)
                    ->sanitizingFileName(function($fileName) {
                        return MediaHelper::sanitizeFilename($fileName);
                    })
                    ->toMediaCollection($collection);
            }
        }
    }

    public function uploadMultipleFromRequest(
        MediaInterface $model,
        Request $request,
        string $requestKey,
        string $collection = 'default'
    ): void {
        if ($request->hasFile($requestKey)) {
            foreach ($request->file($requestKey) as $file) {
                // Security checks
                if ($this->isSecureFile($file)) {
                    $model->addMedia($file)
                        ->sanitizingFileName(function($fileName) {
                            return MediaHelper::sanitizeFilename($fileName);
                        })
                        ->toMediaCollection($collection);
                }
            }
        }
    }

    public function clearMediaCollection(MediaInterface $model, string $collection = 'default'): void
    {
        $model->clearMediaCollection($collection);
    }

    private function isSecureFile(UploadedFile $file): bool
    {
        if (MediaHelper::isDangerousFile($file)) {
            return false;
        }

        if (!MediaHelper::validateFileHeaders($file)) {
            return false;
        }

        return true;
    }
}
