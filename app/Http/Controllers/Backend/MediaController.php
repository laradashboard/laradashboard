<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Support\Helper\MediaHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\MediaBulkDeleteRequest;
use App\Http\Requests\Backend\MediaUploadRequest;
use App\Models\Media;
use App\Services\MediaLibraryService;
use Illuminate\Http\Request;

class MediaController extends Controller
{
    public function __construct(private readonly MediaLibraryService $mediaLibraryService)
    {
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Media::class);

        // Check for PHP upload limit errors first
        $phpError = MediaHelper::checkPhpUploadError();
        if ($phpError) {
            return redirect()->back()->withErrors([
                'upload_error' => $phpError['message'],
            ])->withInput();
        }

        $breadcrumbs = [
            'title' => __('Media Library'),
            'links' => [
                [
                    'name' => __('Dashboard'),
                    'url' => route('admin.dashboard'),
                ],
                [
                    'name' => __('Media Library'),
                    'url' => '#',
                ],
            ],
        ];

        $result = $this->mediaLibraryService->getMediaList(
            $request->get('search'),
            $request->get('type'),
            $request->get('sort', 'created_at'),
            $request->get('direction', 'desc'),
            50
        );

        // Transform media items to include proper URLs.
        $result['media']->getCollection()->transform(function ($item) {
            try {
                if (empty($item->model_type) || $item->model_id == 0) {
                    $item->url = asset('storage/media/' . $item->file_name);
                    $item->thumb_url = $item->url;
                } else {
                    $item->url = $item->getUrl();
                    $item->thumb_url = $item->hasGeneratedConversion('thumb') ? $item->getUrl('thumb') : $item->getUrl();
                }
            } catch (\Exception $e) {
                $item->url = asset('storage/media/' . $item->file_name);
                $item->thumb_url = $item->url;
            }

            return $item;
        });

        // Get upload limits for frontend
        $uploadLimits = MediaHelper::getUploadLimits();

        return view('backend.pages.media.index', [
            'media' => $result['media'],
            'breadcrumbs' => $breadcrumbs,
            'stats' => $result['stats'],
            'uploadLimits' => $uploadLimits,
        ]);
    }

    public function store(MediaUploadRequest $request)
    {
        $this->authorize('create', Media::class);

        if (config('app.demo_mode', false) && Media::count() > 10) {
            return response()->json([
                'success' => false,
                'message' => __('More than 10 media items are not allowed in demo mode. To test, please either delete some existing items and try again or test on your local/live environment.'),
            ], 403);
        }

        // Double-check for PHP upload errors in case they weren't caught earlier
        $phpError = MediaHelper::checkPhpUploadError();
        if ($phpError) {
            return response()->json([
                'success' => false,
                'message' => $phpError['message'],
                'error_type' => 'php_upload_limit',
                'uploaded_size' => $phpError['uploaded_size'],
                'limit' => $phpError['limit'],
                'limit_formatted' => $phpError['limit_formatted'],
            ], 422);
        }

        try {
            $uploadedFiles = $this->mediaLibraryService->uploadMedia($request->file('files', []));

            return response()->json([
                'success' => true,
                'message' => __('Files uploaded successfully'),
                'files' => $uploadedFiles,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => __('Upload validation failed'),
                'errors' => $e->errors(),
                'error_type' => 'validation_failed',
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('Upload failed: :error', ['error' => $e->getMessage()]),
                'error_type' => 'upload_failed',
            ], 500);
        }
    }

    public function destroy($id)
    {
        $media = Media::findOrFail($id);
        $this->authorize('delete', $media);

        $this->mediaLibraryService->deleteMedia($id);

        return response()->json([
            'success' => true,
            'message' => __('Media deleted successfully'),
        ]);
    }

    public function bulkDelete(MediaBulkDeleteRequest $request)
    {
        $this->authorize('bulkDelete', Media::class);

        $this->mediaLibraryService->bulkDeleteMedia($request->ids);

        return redirect()->back()->with('success', __('Selected media deleted successfully'));
    }

    public function api(Request $request)
    {
        $this->authorize('viewAny', Media::class);

        $result = $this->mediaLibraryService->getMediaList(
            $request->get('search'),
            $request->get('type'),
            $request->get('sort', 'created_at'),
            $request->get('direction', 'desc'),
            (int) $request->get('per_page', 100)
        );

        // Transform media for API response.
        $mediaItems = $result['media']->map(function ($item) {
            $url = '';
            $thumbnailUrl = '';

            try {
                if (empty($item->model_type) || $item->model_id == 0) {
                    $url = asset('storage/media/' . $item->file_name);
                    $thumbnailUrl = $url;
                } else {
                    $url = $item->getUrl();
                    $thumbnailUrl = $item->hasGeneratedConversion('thumb') ? $item->getUrl('thumb') : $item->getUrl();
                }
            } catch (\Exception $e) {
                $url = asset('storage/media/' . $item->file_name);
                $thumbnailUrl = $url;
            }

            return [
                'id' => $item->id,
                'name' => $item->name,
                'file_name' => $item->file_name,
                'mime_type' => $item->mime_type,
                'size' => $item->size,
                'human_readable_size' => $item->human_readable_size,
                'url' => $url,
                'thumbnail_url' => $thumbnailUrl,
                'extension' => pathinfo($item->file_name, PATHINFO_EXTENSION),
                'created_at' => $item->created_at->format('Y-m-d H:i:s'),
                'collection_name' => $item->collection_name ?? 'default',
                'model_type' => $item->model_type,
                'model_id' => $item->model_id,
                'is_standalone' => empty($item->model_type) || $item->model_id == 0,
            ];
        });

        return response()->json([
            'success' => true,
            'media' => $mediaItems,
            'stats' => $result['stats'],
            'pagination' => [
                'current_page' => $result['media']->currentPage(),
                'last_page' => $result['media']->lastPage(),
                'per_page' => $result['media']->perPage(),
                'total' => $result['media']->total(),
                'has_more_pages' => $result['media']->hasMorePages(),
            ],
        ]);
    }

    /**
     * Get upload limits for frontend consumption
     */
    public function getUploadLimits()
    {
        $this->authorize('viewAny', Media::class);

        $limits = MediaHelper::getUploadLimits();

        // Add demo mode restrictions info
        if (config('app.demo_mode', false)) {
            $limits['demo_mode'] = true;
            $limits['allowed_mime_types'] = MediaHelper::getAllowedMimeTypesForDemo();
            $limits['demo_restriction_message'] = __('In demo mode, only images, videos, PDFs, and documents (Word, Excel, PowerPoint, text files) are allowed.');
        } else {
            $limits['demo_mode'] = false;
        }

        return response()->json([
            'success' => true,
            'limits' => $limits,
        ]);
    }
}
