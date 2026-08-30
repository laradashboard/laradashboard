<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PublicStorageController extends Controller
{
    /**
     * Serve a file from the public disk with security headers.
     *
     * Used by the test HTTP kernel and as a fallback when the /storage
     * symlink is not served as a static file. Production Apache/Nginx
     * typically serve existing files directly; matching headers should
     * also be configured at the web server (see public/.htaccess).
     */
    public function show(string $path): BinaryFileResponse
    {
        $path = $this->normalizePath($path);

        if ($path === null || ! Storage::disk('public')->exists($path)) {
            abort(404);
        }

        $fullPath = Storage::disk('public')->path($path);
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        $headers = [
            'X-Content-Type-Options' => 'nosniff',
        ];

        if ($extension === 'svg') {
            $headers['Content-Type'] = 'image/svg+xml';
            $headers['Content-Security-Policy'] = "default-src 'none'; img-src 'self'; style-src 'unsafe-inline'; sandbox";
        }

        return response()->file($fullPath, $headers);
    }

    protected function normalizePath(string $path): ?string
    {
        $path = str_replace('\\', '/', $path);
        $path = ltrim($path, '/');

        if ($path === '' || str_contains($path, '..') || str_contains($path, "\0")) {
            return null;
        }

        return $path;
    }
}
