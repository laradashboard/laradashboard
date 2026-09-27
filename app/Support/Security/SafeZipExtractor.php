<?php

declare(strict_types=1);

namespace App\Support\Security;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use ZipArchive;

class SafeZipExtractor
{
    /**
     * Extract a zip archive without allowing path traversal (zip-slip).
     */
    public function extract(string $zipPath, string $destination): bool
    {
        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== true) {
            return false;
        }

        try {
            if (! File::exists($destination)) {
                File::makeDirectory($destination, 0755, true);
            }

            $destinationReal = realpath($destination);
            if ($destinationReal === false) {
                return false;
            }

            for ($i = 0; $i < $zip->numFiles; $i++) {
                $entryName = $zip->getNameIndex($i);
                if ($entryName === false) {
                    Log::warning('Rejected zip archive with invalid entry index', [
                        'index' => $i,
                        'zip' => $zipPath,
                    ]);

                    return false;
                }

                $relativePath = $this->normalizeEntryPath($entryName);
                if ($relativePath === null) {
                    Log::warning('Rejected zip entry with unsafe path', [
                        'entry' => $entryName,
                        'zip' => $zipPath,
                    ]);

                    return false;
                }

                $targetPath = $destinationReal.DIRECTORY_SEPARATOR.$relativePath;

                if (! $this->pathIsInsideDirectory($targetPath, $destinationReal)) {
                    Log::warning('Rejected zip entry outside extraction directory', [
                        'entry' => $entryName,
                        'zip' => $zipPath,
                    ]);

                    return false;
                }

                if ($this->entryIsDirectory($entryName)) {
                    File::ensureDirectoryExists($targetPath);

                    continue;
                }

                File::ensureDirectoryExists(dirname($targetPath));

                $contents = $zip->getFromIndex($i);
                if ($contents === false) {
                    return false;
                }

                File::put($targetPath, $contents);
            }

            return true;
        } finally {
            $zip->close();
        }
    }

    /**
     * Normalize a zip entry name to a safe relative path, or null when unsafe.
     */
    public function normalizeEntryPath(string $entryName): ?string
    {
        $entryName = str_replace('\\', '/', $entryName);

        if (str_contains($entryName, "\0")) {
            return null;
        }

        if (str_starts_with($entryName, '/') || preg_match('/^[a-zA-Z]:\\//', $entryName) === 1) {
            return null;
        }

        $parts = explode('/', $entryName);
        $safeParts = [];

        foreach ($parts as $part) {
            if ($part === '' || $part === '.') {
                continue;
            }

            if ($part === '..') {
                return null;
            }

            $safeParts[] = $part;
        }

        if ($safeParts === []) {
            return null;
        }

        return implode(DIRECTORY_SEPARATOR, $safeParts);
    }

    private function entryIsDirectory(string $entryName): bool
    {
        return str_ends_with(str_replace('\\', '/', $entryName), '/');
    }

    private function pathIsInsideDirectory(string $path, string $directory): bool
    {
        $directory = rtrim(str_replace('\\', '/', $directory), '/');
        $path = str_replace('\\', '/', $path);

        return $path === $directory || str_starts_with($path, $directory.'/');
    }
}
