<?php

declare(strict_types=1);

namespace App\Concerns;

use App\Support\Helper\MediaHelper;
use Illuminate\Http\UploadedFile;

trait HandlesMediaOperations
{
    /**
     * Format file size from bytes to human readable format
     */
    protected function formatFileSize(int $bytes): string
    {
        return MediaHelper::formatFileSize($bytes);
    }

    /**
     * Get file type category based on mime type
     */
    protected function getFileTypeCategory(string $mimeType): string
    {
        return MediaHelper::getFileTypeCategory($mimeType);
    }

    /**
     * Get media icon based on mime type
     */
    protected function getMediaIcon(string $mimeType): string
    {
        return MediaHelper::getMediaIcon($mimeType);
    }

    /**
     * Sanitize filename for safe storage
     */
    protected function sanitizeFilename(string $filename): string
    {
        return MediaHelper::sanitizeFilename($filename);
    }

    /**
     * Reject files whose extension is not on the media allowlist.
     */
    protected function isDangerousFile(UploadedFile $file): bool
    {
        return MediaHelper::isDangerousFile($file);
    }

    /**
     * Validate server-detected MIME type matches the file extension.
     */
    protected function validateFileHeaders(UploadedFile $file): bool
    {
        return MediaHelper::validateFileHeaders($file);
    }

    /**
     * Generate a unique, secure filename
     */
    protected function generateUniqueFilename(string $originalName): string
    {
        return MediaHelper::generateUniqueFilename($originalName);
    }

    /**
     * Check if uploaded file passes security checks
     */
    protected function isSecureFile(UploadedFile $file): bool
    {
        if ($this->isDangerousFile($file)) {
            return false;
        }

        if (! $this->validateFileHeaders($file)) {
            return false;
        }

        return true;
    }
}
