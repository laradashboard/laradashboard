<?php

declare(strict_types=1);

namespace App\Enums\Hooks;

/**
 * Media Action Hooks
 *
 * Provides action hooks for media/file events that modules can hook into.
 * Action hooks are fire-and-forget - they notify listeners but don't expect return values.
 *
 * @example
 * // Optimize images after upload
 * Hook::addAction(MediaActionHook::MEDIA_UPLOADED_AFTER, function ($media) {
 *     dispatch(new OptimizeImageJob($media));
 * });
 */
enum MediaActionHook: string
{
    // ==========================================================================
    // Media Upload Events
    // ==========================================================================

    /**
     * Fired before a media file is uploaded.
     *
     * @param \Illuminate\Http\UploadedFile $file The uploaded file
     * @param string $collection The collection name
     */
    case MEDIA_UPLOADING_BEFORE = 'action.media.uploading_before';

    /**
     * Fired after a media file is successfully uploaded.
     *
     * @param mixed $media The media model
     */
    case MEDIA_UPLOADED_AFTER = 'action.media.uploaded_after';

    /**
     * Fired when a media upload fails.
     *
     * @param \Illuminate\Http\UploadedFile $file The file
     * @param \Throwable $exception The exception
     */
    case MEDIA_UPLOAD_FAILED = 'action.media.upload_failed';

    // ==========================================================================
    // Media CRUD Events
    // ==========================================================================

    /**
     * Fired before a media record is created.
     *
     * @param array $data The media data
     */
    case MEDIA_CREATED_BEFORE = 'action.media.created_before';

    /**
     * Fired after a media record is created.
     *
     * @param mixed $media The created media
     */
    case MEDIA_CREATED_AFTER = 'action.media.created_after';

    /**
     * Fired before a media record is updated.
     *
     * @param mixed $media The media being updated
     * @param array $data The new data
     */
    case MEDIA_UPDATED_BEFORE = 'action.media.updated_before';

    /**
     * Fired after a media record is updated.
     *
     * @param mixed $media The updated media
     */
    case MEDIA_UPDATED_AFTER = 'action.media.updated_after';

    /**
     * Fired before a media file is deleted.
     *
     * @param mixed $media The media being deleted
     */
    case MEDIA_DELETED_BEFORE = 'action.media.deleted_before';

    /**
     * Fired after a media file is deleted.
     *
     * @param int $mediaId The ID of the deleted media
     */
    case MEDIA_DELETED_AFTER = 'action.media.deleted_after';

    // ==========================================================================
    // Image Processing Events
    // ==========================================================================

    /**
     * Fired before image conversions are generated.
     *
     * @param mixed $media The media
     */
    case MEDIA_CONVERSIONS_BEFORE = 'action.media.conversions_before';

    /**
     * Fired after image conversions are generated.
     *
     * @param mixed $media The media
     * @param array $conversions The generated conversions
     */
    case MEDIA_CONVERSIONS_AFTER = 'action.media.conversions_after';

    // ==========================================================================
    // Bulk Operations
    // ==========================================================================

    /**
     * Fired before media files are bulk deleted.
     *
     * @param array $mediaIds The IDs being deleted
     */
    case MEDIA_BULK_DELETED_BEFORE = 'action.media.bulk_deleted_before';

    /**
     * Fired after media files are bulk deleted.
     *
     * @param array $mediaIds The deleted IDs
     */
    case MEDIA_BULK_DELETED_AFTER = 'action.media.bulk_deleted_after';

    // ==========================================================================
    // Media Association Events
    // ==========================================================================

    /**
     * Fired when media is associated with a model.
     *
     * @param mixed $media The media
     * @param mixed $model The model it's associated with
     */
    case MEDIA_ASSOCIATED = 'action.media.associated';

    /**
     * Fired when media is disassociated from a model.
     *
     * @param mixed $media The media
     * @param mixed $model The model
     */
    case MEDIA_DISASSOCIATED = 'action.media.disassociated';
}
