<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class ImageService
{
    public function getSvgIcon(string $name, string $classes = '', string $fallback = ''): string
    {
        // if name includes .svg, remove it
        $name = Str::replaceLast('.svg', '', $name);

        $path = public_path("images/icons/{$name}.svg");

        if (file_exists($path)) {
            $svg = file_get_contents($path);

            return Str::replaceFirst(
                '<svg',
                '<svg class="' . e($classes) . '"',
                $svg
            );
        }

        // Fallback: Iconify icon
        if ($fallback) {
            return '<iconify-icon icon="lucide:' . e($fallback) . '" class="' . e($classes) . '"></iconify-icon>';
        }

        // If no SVG and no fallback.
        return '';
    }

    /**
     * Image extensions that may be stored in the public web root, mapped to
     * the server-detected MIME types accepted for each.
     *
     * Never derive the stored extension from anything outside this map: the
     * target directory is served directly by the web server.
     *
     * @var array<string, list<string>>
     */
    public const ALLOWED_IMAGE_TYPES = [
        'jpg' => ['image/jpeg', 'image/pjpeg'],
        'jpeg' => ['image/jpeg', 'image/pjpeg'],
        'png' => ['image/png'],
        'gif' => ['image/gif'],
        'webp' => ['image/webp'],
        'avif' => ['image/avif'],
        'bmp' => ['image/bmp', 'image/x-ms-bmp'],
        'ico' => ['image/vnd.microsoft.icon', 'image/x-icon', 'image/ico'],
        'svg' => ['image/svg+xml', 'image/svg', 'text/xml', 'application/xml', 'text/plain', 'text/html'],
    ];

    public function __construct(private readonly ?SvgSanitizer $svgSanitizer = null)
    {
    }

    /**
     * Store an uploaded image in the public path and return its URL.
     *
     * @throws ValidationException When the upload is not an allowed image.
     */
    public function storeImageAndGetUrl($input, string $fileKey, string $path): ?string
    {
        $file = null;

        if ($input instanceof \Illuminate\Http\Request && $input->hasFile($fileKey)) {
            $file = $input->file($fileKey);
        } elseif (is_array($input) && isset($input[$fileKey]) && $input[$fileKey] instanceof UploadedFile) {
            $file = $input[$fileKey];
        }

        if (! $file instanceof UploadedFile) {
            return null;
        }

        $extension = $this->assertAllowedImage($file, $fileKey);

        $fileName = uniqid($fileKey . '_') . '.' . $extension;
        $targetPath = public_path($path);

        if (! file_exists($targetPath)) {
            mkdir($targetPath, 0755, true);
        }

        if ($extension === 'svg') {
            try {
                $sanitized = ($this->svgSanitizer ?? app(SvgSanitizer::class))->sanitizeUploadedFile($file);
            } catch (RuntimeException $e) {
                throw ValidationException::withMessages([$fileKey => $e->getMessage()]);
            }

            File::put($targetPath . DIRECTORY_SEPARATOR . $fileName, $sanitized);
        } else {
            $file->move($targetPath, $fileName);
        }

        return asset($path . '/' . $fileName);
    }

    /**
     * Ensure the upload is a genuine image of an allowed type and return the
     * normalised extension to store it under.
     *
     * @throws ValidationException
     */
    protected function assertAllowedImage(UploadedFile $file, string $fileKey): string
    {
        $reject = fn () => ValidationException::withMessages([
            $fileKey => __('The :attribute must be an image of type: :types.', [
                'attribute' => str_replace('_', ' ', $fileKey),
                'types' => implode(', ', array_keys(self::ALLOWED_IMAGE_TYPES)),
            ]),
        ]);

        if (! $file->isValid()) {
            throw $reject();
        }

        $extension = strtolower($file->getClientOriginalExtension());

        if (! array_key_exists($extension, self::ALLOWED_IMAGE_TYPES)) {
            throw $reject();
        }

        // Server-side content sniffing, never the client-supplied MIME type.
        $mimeType = (string) $file->getMimeType();

        if (! in_array($mimeType, self::ALLOWED_IMAGE_TYPES[$extension], true)) {
            throw $reject();
        }

        // Raster formats must decode as an image (SVG is validated by the sanitizer).
        if ($extension !== 'svg' && $extension !== 'ico' && @getimagesize((string) $file->getRealPath()) === false) {
            throw $reject();
        }

        return $extension;
    }

    public function deleteImageFromPublic(string $imageUrl)
    {
        $urlParts = parse_url($imageUrl);
        $filePath = ltrim((string) ($urlParts['path'] ?? ''), '/');

        // Only ever delete files this service could have uploaded. Setting
        // values are user-editable, so never trust them to point elsewhere.
        if (
            $filePath === ''
            || str_contains($filePath, '..')
            || ! preg_match('#^(images/)?uploads/#', $filePath)
            || ! is_file(public_path($filePath))
        ) {
            Log::warning('Skipped deleting image (missing or outside uploads): ' . $filePath);
            return false;
        }

        try {
            $fileDeleted = File::delete(public_path($filePath));
            if (! $fileDeleted) {
                Log::error('Failed to delete file: ' . $filePath);
            }

            return $fileDeleted;
        } catch (\Throwable $th) {
            Log::error('Error deleting file: ' . $filePath . ' - ' . $th->getMessage());
            return false;
        }
    }
}
