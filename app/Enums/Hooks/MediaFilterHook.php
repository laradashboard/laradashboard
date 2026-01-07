<?php

declare(strict_types=1);

namespace App\Enums\Hooks;

/**
 * Media Filter Hooks
 *
 * Provides filter hooks for media/files that modules can use to modify behavior.
 * Filter hooks receive a value and must return it (modified or not).
 *
 * @example
 * // Add custom allowed extensions
 * Hook::addFilter(MediaFilterHook::MEDIA_ALLOWED_EXTENSIONS, function ($extensions) {
 *     $extensions[] = 'webp';
 *     return $extensions;
 * });
 */
enum MediaFilterHook: string
{
    // ==========================================================================
    // Upload Filters
    // ==========================================================================

    /**
     * Filter the file before upload.
     *
     * @param \Illuminate\Http\UploadedFile $file The file
     * @return \Illuminate\Http\UploadedFile Modified file
     */
    case MEDIA_UPLOAD_FILE = 'filter.media.upload.file';

    /**
     * Filter the upload path/directory.
     *
     * @param string $path The upload path
     * @return string Modified path
     */
    case MEDIA_UPLOAD_PATH = 'filter.media.upload.path';

    /**
     * Filter the filename before saving.
     *
     * @param string $filename The filename
     * @return string Modified filename
     */
    case MEDIA_UPLOAD_FILENAME = 'filter.media.upload.filename';

    // ==========================================================================
    // File Validation Filters
    // ==========================================================================

    /**
     * Filter allowed file extensions.
     *
     * @param array $extensions The allowed extensions
     * @return array Modified extensions
     */
    case MEDIA_ALLOWED_EXTENSIONS = 'filter.media.allowed_extensions';

    /**
     * Filter allowed MIME types.
     *
     * @param array $mimeTypes The allowed MIME types
     * @return array Modified MIME types
     */
    case MEDIA_ALLOWED_MIME_TYPES = 'filter.media.allowed_mime_types';

    /**
     * Filter the maximum file size (in bytes).
     *
     * @param int $maxSize The max size
     * @return int Modified max size
     */
    case MEDIA_MAX_FILE_SIZE = 'filter.media.max_file_size';

    /**
     * Filter the maximum image dimensions.
     *
     * @param array $dimensions ['width' => int, 'height' => int]
     * @return array Modified dimensions
     */
    case MEDIA_MAX_DIMENSIONS = 'filter.media.max_dimensions';

    // ==========================================================================
    // Image Processing Filters
    // ==========================================================================

    /**
     * Filter image conversion settings.
     *
     * @param array $conversions The conversion settings
     * @return array Modified conversions
     */
    case MEDIA_IMAGE_CONVERSIONS = 'filter.media.image.conversions';

    /**
     * Filter image quality setting.
     *
     * @param int $quality The quality (1-100)
     * @return int Modified quality
     */
    case MEDIA_IMAGE_QUALITY = 'filter.media.image.quality';

    /**
     * Filter image optimization settings.
     *
     * @param array $settings The optimization settings
     * @return array Modified settings
     */
    case MEDIA_IMAGE_OPTIMIZATION = 'filter.media.image.optimization';

    // ==========================================================================
    // Data Filters
    // ==========================================================================

    /**
     * Filter media data before creation.
     *
     * @param array $data The media data
     * @return array Modified data
     */
    case MEDIA_CREATED_BEFORE = 'filter.media.created_before';

    /**
     * Filter media after creation.
     *
     * @param mixed $media The media
     * @return mixed Modified media
     */
    case MEDIA_CREATED_AFTER = 'filter.media.created_after';

    /**
     * Filter media data before update.
     *
     * @param array $data The media data
     * @return array Modified data
     */
    case MEDIA_UPDATED_BEFORE = 'filter.media.updated_before';

    /**
     * Filter media after update.
     *
     * @param mixed $media The media
     * @return mixed Modified media
     */
    case MEDIA_UPDATED_AFTER = 'filter.media.updated_after';

    // ==========================================================================
    // Collection Filters
    // ==========================================================================

    /**
     * Filter available media collections.
     *
     * @param array $collections The collections
     * @return array Modified collections
     */
    case MEDIA_COLLECTIONS = 'filter.media.collections';

    /**
     * Filter media collection configuration.
     *
     * @param array $config The collection config
     * @param string $collection The collection name
     * @return array Modified config
     */
    case MEDIA_COLLECTION_CONFIG = 'filter.media.collection.config';

    // ==========================================================================
    // Validation Hooks
    // ==========================================================================

    /**
     * Filter validation rules for media upload.
     *
     * @param array $rules The validation rules
     * @return array Modified rules
     */
    case MEDIA_UPLOAD_VALIDATION_RULES = 'filter.media.upload.validation.rules';

    // ==========================================================================
    // UI Hooks
    // ==========================================================================

    /**
     * Hook after the media library breadcrumbs.
     */
    case MEDIA_AFTER_BREADCRUMBS = 'filter.media.after_breadcrumbs';

    /**
     * Hook after the media library grid.
     */
    case MEDIA_AFTER_GRID = 'filter.media.after_grid';

    /**
     * Hook for custom tabs in the media library.
     */
    case MEDIA_LIBRARY_TABS = 'filter.media.library.tabs';

    /**
     * Hook for custom actions in media item context menu.
     */
    case MEDIA_ITEM_ACTIONS = 'filter.media.item.actions';
}
