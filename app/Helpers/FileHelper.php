<?php

declare(strict_types=1);

namespace App\Helpers;

class FileHelper
{
    /**
     * Convert PHP size string (e.g., '2M', '128K', '1G') to bytes.
     */
    public static function convertToBytes(string $size): int
    {
        $size = trim($size);
        $unit = strtolower(substr($size, -1));
        $value = (int) $size;

        return match ($unit) {
            'g' => $value * 1024 * 1024 * 1024,
            'm' => $value * 1024 * 1024,
            'k' => $value * 1024,
            default => $value,
        };
    }

    /**
     * Format bytes to human readable string.
     */
    public static function formatBytes(int $bytes): string
    {
        if ($bytes >= 1024 * 1024 * 1024) {
            return number_format($bytes / (1024 * 1024 * 1024), 2) . ' GB';
        }
        if ($bytes >= 1024 * 1024) {
            return number_format($bytes / (1024 * 1024), 2) . ' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }

        return $bytes . ' bytes';
    }

    /**
     * Get the effective maximum upload size based on PHP configuration.
     */
    public static function getMaxUploadSize(): int
    {
        $uploadMaxFilesize = ini_get('upload_max_filesize');
        $postMaxSize = ini_get('post_max_size');

        return min(
            self::convertToBytes($uploadMaxFilesize ?: '2M'),
            self::convertToBytes($postMaxSize ?: '8M')
        );
    }

    /**
     * Get the formatted maximum upload size.
     */
    public static function getMaxUploadSizeFormatted(): string
    {
        return self::formatBytes(self::getMaxUploadSize());
    }
}
