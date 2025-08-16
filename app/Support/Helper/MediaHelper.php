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

    public static function isDangerousFile(UploadedFile $file): bool
    {
        $dangerousExtensions = [
            'php',
            'php3',
            'php4',
            'php5',
            'phtml',
            'phps',
            'asp',
            'aspx',
            'jsp',
            'jspx',
            'exe',
            'com',
            'bat',
            'cmd',
            'scr',
            'vbs',
            'vbe',
            'js',
            'jar',
            'pl',
            'py',
            'rb',
            'sh',
        ];

        $extension = strtolower($file->getClientOriginalExtension());

        if (in_array($extension, $dangerousExtensions)) {
            return true;
        }

        // Check for double extensions
        $filename = $file->getClientOriginalName();
        if (preg_match('/\.(php|asp|jsp|exe|com|bat|cmd|scr|vbs|vbe|js|jar|pl|py|rb|sh)\./i', $filename)) {
            return true;
        }

        return false;
    }

    public static function validateFileHeaders(UploadedFile $file): bool
    {
        $mimeType = $file->getMimeType();
        $extension = strtolower($file->getClientOriginalExtension());

        $validMimeTypes = [
            'jpg' => ['image/jpeg'],
            'jpeg' => ['image/jpeg'],
            'png' => ['image/png'],
            'gif' => ['image/gif'],
            'webp' => ['image/webp'],
            'svg' => ['image/svg+xml'],
            'pdf' => ['application/pdf'],
            'mp4' => ['video/mp4'],
            'avi' => ['video/avi', 'video/x-msvideo'],
            'mov' => ['video/quicktime'],
            'mp3' => ['audio/mpeg'],
            'wav' => ['audio/wav', 'audio/x-wav'],
            'ogg' => ['audio/ogg'],
        ];

        if (! isset($validMimeTypes[$extension])) {
            return true; // Allow unknown extensions
        }

        return in_array($mimeType, $validMimeTypes[$extension]);
    }

    public static function generateUniqueFilename(string $originalName): string
    {
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $name = pathinfo($originalName, PATHINFO_FILENAME);

        $sanitizedName = self::sanitizeFilename($name);
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
            $_SERVER['REQUEST_METHOD'] === 'POST' &&
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
     * Get allowed MIME types for demo mode
     * Only allows images, videos, PDFs, and documents
     */
    public static function getAllowedMimeTypesForDemo(): array
    {
        return [
            // Images
            'image/jpeg',
            'image/jpg',
            'image/png',
            'image/gif',
            'image/webp',
            'image/svg+xml',
            'image/bmp',
            'image/tiff',

            // Videos
            'video/mp4',
            'video/avi',
            'video/quicktime',
            'video/x-msvideo',
            'video/webm',
            'video/ogg',
            'video/3gpp',
            'video/x-ms-wmv',

            // Audios
            'audio/mpeg',
            'audio/wav',
            'audio/ogg',
            'audio/aac',
            'audio/flac',

            // PDFs
            'application/pdf',

            // Documents
            'application/msword', // .doc
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // .docx
            'application/vnd.ms-excel', // .xls
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // .xlsx
            'application/vnd.ms-powerpoint', // .ppt
            'application/vnd.openxmlformats-officedocument.presentationml.presentation', // .pptx
            'text/plain', // .txt
            'text/csv', // .csv
            'application/rtf', // .rtf
        ];
    }

    /**
     * Check if a file type is allowed in demo mode
     */
    public static function isAllowedInDemoMode(string $mimeType): bool
    {
        if (! config('app.demo_mode', false)) {
            return true; // No restrictions when not in demo mode
        }

        return in_array($mimeType, self::getAllowedMimeTypesForDemo());
    }
}
