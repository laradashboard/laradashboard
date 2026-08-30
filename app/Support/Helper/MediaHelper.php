<?php

declare(strict_types=1);

namespace App\Support\Helper;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class MediaHelper
{
    public static function formatFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    public static function getFileTypeCategory(string $mimeType): string
    {
        $categories = [
            'image' => ['image/'],
            'video' => ['video/'],
            'audio' => ['audio/'],
            'document' => [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument',
                'text/',
            ],
            'archive' => [
                'application/zip',
                'application/x-rar',
                'application/x-7z',
            ],
        ];

        foreach ($categories as $category => $patterns) {
            foreach ($patterns as $pattern) {
                if (str_starts_with($mimeType, $pattern)) {
                    return $category;
                }
            }
        }

        return 'other';
    }

    public static function sanitizeFilename(string $filename): string
    {
        // Remove path traversal attempts
        $filename = basename($filename);

        // Remove dangerous characters
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);

        // Prevent double extensions
        $filename = preg_replace('/\.+/', '.', $filename);

        // Ensure filename is not too long
        if (strlen($filename) > 255) {
            $extension = pathinfo($filename, PATHINFO_EXTENSION);
            $name = pathinfo($filename, PATHINFO_FILENAME);
            $filename = substr($name, 0, 255 - strlen($extension) - 1) . '.' . $extension;
        }

        return $filename;
    }

    /**
     * Allowed media extensions mapped to server-detected MIME types.
     *
     * Client-supplied Content-Type is never used for these checks.
     *
     * @return array<string, list<string>>
     */
    public static function getAllowedExtensionMimeMap(): array
    {
        return [
            'jpg' => ['image/jpeg', 'image/jpg'],
            'jpeg' => ['image/jpeg', 'image/jpg'],
            'png' => ['image/png'],
            'gif' => ['image/gif'],
            'webp' => ['image/webp'],
            'svg' => ['image/svg+xml', 'image/svg', 'text/xml', 'application/xml', 'text/plain'],
            'bmp' => ['image/bmp', 'image/x-ms-bmp'],
            'tif' => ['image/tiff'],
            'tiff' => ['image/tiff'],
            'pdf' => ['application/pdf'],
            'mp4' => ['video/mp4'],
            'avi' => ['video/avi', 'video/x-msvideo'],
            'mov' => ['video/quicktime'],
            'webm' => ['video/webm'],
            'ogv' => ['video/ogg'],
            '3gp' => ['video/3gpp'],
            'wmv' => ['video/x-ms-wmv'],
            'mp3' => ['audio/mpeg', 'audio/mp3'],
            'wav' => ['audio/wav', 'audio/x-wav', 'audio/wave'],
            'ogg' => ['audio/ogg', 'video/ogg', 'application/ogg'],
            'aac' => ['audio/aac', 'audio/x-aac'],
            'flac' => ['audio/flac', 'audio/x-flac'],
            'doc' => ['application/msword'],
            'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
            'xls' => ['application/vnd.ms-excel'],
            'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
            'ppt' => ['application/vnd.ms-powerpoint'],
            'pptx' => ['application/vnd.openxmlformats-officedocument.presentationml.presentation'],
            'txt' => ['text/plain'],
            'csv' => ['text/csv', 'text/plain', 'application/csv', 'text/x-csv'],
            'rtf' => ['application/rtf', 'text/rtf'],
        ];
    }

    /**
     * @return list<string>
     */
    public static function getAllowedExtensions(): array
    {
        return array_keys(self::getAllowedExtensionMimeMap());
    }

    /**
     * @return list<string>
     */
    public static function getAllowedMimeTypes(): array
    {
        $types = [];

        foreach (self::getAllowedExtensionMimeMap() as $mimes) {
            foreach ($mimes as $mime) {
                $types[] = $mime;
            }
        }

        return array_values(array_unique($types));
    }

    public static function hasAllowedExtension(UploadedFile $file): bool
    {
        $extension = strtolower($file->getClientOriginalExtension());

        return $extension !== '' && array_key_exists($extension, self::getAllowedExtensionMimeMap());
    }

    public static function isSvgFile(UploadedFile $file): bool
    {
        return strtolower($file->getClientOriginalExtension()) === 'svg';
    }

    public static function isDangerousFile(UploadedFile $file): bool
    {
        return ! self::hasAllowedExtension($file);
    }

    public static function validateFileHeaders(UploadedFile $file): bool
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $map = self::getAllowedExtensionMimeMap();

        if ($extension === '' || ! isset($map[$extension])) {
            return false;
        }

        $mimeType = $file->getMimeType();

        if (! is_string($mimeType) || $mimeType === '') {
            return false;
        }

        return in_array($mimeType, $map[$extension], true);
    }

    public static function generateUniqueFilename(string $originalName): string
    {
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $name = pathinfo($originalName, PATHINFO_FILENAME);

        $sanitizedName = self::sanitizeFilename($name);
        $sanitizedName = str_replace('.', '_', $sanitizedName);
        $sanitizedName = trim($sanitizedName, '._-');

        if ($sanitizedName === '') {
            $sanitizedName = 'file';
        }

        if ($extension === '' || ! array_key_exists($extension, self::getAllowedExtensionMimeMap())) {
            $extension = 'bin';
        }

        $timestamp = now()->format('Y-m-d_H-i-s');
        $random = Str::random(8);

        return "{$sanitizedName}_{$timestamp}_{$random}.{$extension}";
    }

    public static function getMediaIcon(string $mimeType): string
    {
        $category = self::getFileTypeCategory($mimeType);

        $icons = [
            'image' => 'fa-image',
            'video' => 'fa-video',
            'audio' => 'fa-music',
            'document' => 'fa-file-text',
            'archive' => 'fa-file-archive',
            'other' => 'fa-file',
        ];

        return $icons[$category] ?? $icons['other'];
    }

    /**
     * Get PHP upload limits from ini settings
     */
    public static function getUploadLimits(): array
    {
        $uploadMaxFilesize = self::parseSize(ini_get('upload_max_filesize'));
        $postMaxSize = self::parseSize(ini_get('post_max_size'));
        $maxFileUploads = (int) ini_get('max_file_uploads');
        $maxInputTime = (int) ini_get('max_input_time');
        $maxExecutionTime = (int) ini_get('max_execution_time');

        // The effective upload limit is the smaller of upload_max_filesize and post_max_size
        $effectiveMaxFilesize = min($uploadMaxFilesize, $postMaxSize);

        return [
            'upload_max_filesize' => $uploadMaxFilesize,
            'upload_max_filesize_formatted' => self::formatFileSize($uploadMaxFilesize),
            'post_max_size' => $postMaxSize,
            'post_max_size_formatted' => self::formatFileSize($postMaxSize),
            'effective_max_filesize' => $effectiveMaxFilesize,
            'effective_max_filesize_formatted' => self::formatFileSize($effectiveMaxFilesize),
            'max_file_uploads' => $maxFileUploads,
            'max_input_time' => $maxInputTime,
            'max_execution_time' => $maxExecutionTime,
            'allowed_mime_types' => self::getAllowedMimeTypes(),
            'allowed_extensions' => self::getAllowedExtensions(),
        ];
    }

    /**
     * Parse PHP size strings (like "8M", "2G") to bytes
     */
    public static function parseSize(string $size): int
    {
        $size = trim($size);
        $last = strtolower($size[strlen($size) - 1]);
        $size = (int) $size;

        switch ($last) {
            case 'g':
                $size *= 1024;
                // no break
            case 'm':
                $size *= 1024;
                // no break
            case 'k':
                $size *= 1024;
        }

        return $size;
    }

    /**
     * Check if the current request might have exceeded PHP limits
     */
    public static function checkPhpUploadError(): ?array
    {
        $limits = self::getUploadLimits();

        // Check if POST was truncated due to post_max_size
        if (
            ($_SERVER['REQUEST_METHOD'] ?? null) === 'POST' &&
            empty($_POST) &&
            empty($_FILES) &&
            isset($_SERVER['CONTENT_LENGTH']) &&
            $_SERVER['CONTENT_LENGTH'] > $limits['post_max_size']
        ) {
            return [
                'error' => 'post_max_size_exceeded',
                'message' => "Upload size (" . self::formatFileSize((int)$_SERVER['CONTENT_LENGTH']) . ") exceeds the post_max_size limit ({$limits['post_max_size_formatted']})",
                'uploaded_size' => (int)$_SERVER['CONTENT_LENGTH'],
                'limit' => $limits['post_max_size'],
                'limit_formatted' => $limits['post_max_size_formatted'],
            ];
        }

        return null;
    }

    /**
     * @deprecated Use getAllowedMimeTypes(). Kept for backward compatibility.
     *
     * @return list<string>
     */
    public static function getAllowedMimeTypesForDemo(): array
    {
        return self::getAllowedMimeTypes();
    }

    /**
     * MIME allowlist is always enforced, including when demo mode is off.
     */
    public static function isAllowedMimeType(?string $mimeType): bool
    {
        if (! is_string($mimeType) || $mimeType === '') {
            return false;
        }

        return in_array($mimeType, self::getAllowedMimeTypes(), true);
    }

    /**
     * @deprecated Use isAllowedMimeType(). MIME restrictions are no longer demo-only.
     */
    public static function isAllowedInDemoMode(string $mimeType): bool
    {
        return self::isAllowedMimeType($mimeType);
    }
}
