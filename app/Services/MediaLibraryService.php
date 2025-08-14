<?php

declare(strict_types=1);

namespace App\Services;

use App\Concerns\HandlesMediaOperations;
use App\Support\Helper\MediaHelper;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Http\Request;
use App\Models\Media;
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
        $query = Media::query()->latest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('file_name', 'like', "%{$search}%")
                    ->orWhere('mime_type', 'like', "%{$search}%");
            });
        }

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

        if (in_array($sort, ['name', 'size', 'created_at', 'mime_type'])) {
            $query->orderBy($sort, $direction);
        }

        $media = $query->paginate($perPage)->withQueryString();

        // Enhance media items with additional information
        $media->getCollection()->transform(function ($item) {
            $item->human_readable_size = $this->formatFileSize($item->size);
            $item->file_type_category = $this->getFileTypeCategory($item->mime_type);
            $item->icon = $this->getMediaIcon($item->mime_type);
            return $item;
        });

        $stats = $this->getMediaStatistics();

        return [
            'media' => $media,
            'stats' => $stats,
        ];
    }

    public function getMediaStatistics(): array
    {
        return [
            'total' => Media::count(),
            'images' => Media::where('mime_type', 'like', 'image/%')->count(),
            'videos' => Media::where('mime_type', 'like', 'video/%')->count(),
            'audio' => Media::where('mime_type', 'like', 'audio/%')->count(),
            'documents' => Media::whereNotLike('mime_type', 'image/%')
                ->whereNotLike('mime_type', 'video/%')
                ->whereNotLike('mime_type', 'audio/%')
                ->count(),
            'total_size' => $this->formatFileSize((int) Media::sum('size')),
        ];
    }

    public function uploadMedia(array $files): array
    {
        $uploadedFiles = [];

        foreach ($files as $file) {
            if (! $this->isSecureFile($file)) {
                continue;
            }

            if (config('app.demo_mode', false)) {
                $mimeType = $file->getMimeType();
                if (! MediaHelper::isAllowedInDemoMode($mimeType)) {
                    throw new \InvalidArgumentException(__('In demo mode, only images, videos, PDFs, and documents are allowed. File type :type is not permitted.', ['type' => $mimeType]));
                }
            }

            $safeFileName = $this->generateUniqueFilename($file->getClientOriginalName());

            $path = $file->storeAs('media', $safeFileName, 'public');

            $mediaItem = Media::create([
                'model_type' => '',
                'model_id' => 0,
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
    ): ?Media {
        if ($request->hasFile($fieldName)) {
            $file = $request->file($fieldName);

            if ($file && $this->isSecureFile($file)) {
                if (config('app.demo_mode', false)) {
                    $mimeType = $file->getMimeType();
                    if (! MediaHelper::isAllowedInDemoMode($mimeType)) {
                        throw new \InvalidArgumentException(__('In demo mode, only images, videos, PDFs, and documents are allowed. File type :type is not permitted.', ['type' => $mimeType]));
                    }
                }

                $spatieMedia = $model->addMedia($file)
                    ->sanitizingFileName(function ($fileName) {
                        return $this->sanitizeFilename($fileName);
                    })
                    ->toMediaCollection($collection);
                return Media::find($spatieMedia->id);
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
                if ($this->isSecureFile($file)) {
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

    public function associateExistingMedia(
        HasMedia $model,
        string $mediaUrlOrId,
        string $collection = 'default'
    ): ?Media {
        $media = null;

        if (is_numeric($mediaUrlOrId)) {
            $media = Media::find($mediaUrlOrId);
        } else {
            $urlPath = parse_url($mediaUrlOrId, PHP_URL_PATH);
            $fileName = basename($urlPath);

            $media = Media::where('file_name', $fileName)->first();

            if (! $media) {
                $media = Media::where('disk', 'public')
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

        try {
            if ($media->model_id == 0) {
                $mediaPath = storage_path('app/public/media/' . $media->file_name);
            } else {
                $mediaPath = $media->getPath();
            }

            if (file_exists($mediaPath)) {
                $spatieMedia = $model
                    ->addMedia($mediaPath)
                    ->preservingOriginal()
                    ->usingName($media->name)
                    ->usingFileName($media->file_name)
                    ->toMediaCollection($collection);

                return Media::find($spatieMedia->id);
            } else {
                $alternativePaths = [
                    storage_path('app/public/' . $media->file_name),
                    storage_path('app/public/uploads/' . $media->file_name),
                    public_path('storage/media/' . $media->file_name),
                    public_path('storage/' . $media->file_name),
                ];

                foreach ($alternativePaths as $altPath) {
                    if (file_exists($altPath)) {
                        $spatieMedia = $model
                            ->addMedia($altPath)
                            ->preservingOriginal()
                            ->usingName($media->name)
                            ->usingFileName($media->file_name)
                            ->toMediaCollection($collection);
                        return Media::find($spatieMedia->id);
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
