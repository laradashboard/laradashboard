<?php

namespace App\Services;

use App\Contacts\MediaInterface;
use Illuminate\Http\Request;

class MediaLibraryService
{
    /**
     * Upload a file from request to a model's media collection
     */
    public function uploadFromRequest(
        MediaInterface $model,
        Request $request,
        string $requestKey,
        string $collection = 'default'
    ): void {
        if ($request->hasFile($requestKey)) {
            $model->addMediaFromRequest($requestKey)
                ->toMediaCollection($collection);
        }
    }

    /**
     * Upload multiple files from request to a model's media collection
     */
    public function uploadMultipleFromRequest(
        MediaInterface $model,
        Request $request,
        string $requestKey,
        string $collection = 'default'
    ): void {
        if ($request->hasFile($requestKey)) {
            foreach ($request->file($requestKey) as $file) {
                $model->addMedia($file)
                    ->toMediaCollection($collection);
            }
        }
    }

    /**
     * Clear a media collection
     */
    public function clearMediaCollection(MediaInterface $model, string $collection = 'default'): void
    {
        $model->clearMediaCollection($collection);
    }
}
