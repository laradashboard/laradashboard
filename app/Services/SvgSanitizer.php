<?php

declare(strict_types=1);

namespace App\Services;

use enshrined\svgSanitize\Sanitizer;
use Illuminate\Http\UploadedFile;
use RuntimeException;

class SvgSanitizer
{
    /**
     * Validate an SVG and return a sanitized copy.
     *
     * Unsafe or malformed documents are rejected. They are never cleaned
     * and stored.
     *
     * @throws RuntimeException
     */
    public function sanitize(string $contents): string
    {
        if ($contents === '' || ! $this->containsSvgElement($contents)) {
            throw new RuntimeException(__('This SVG file is invalid or malformed and cannot be uploaded.'));
        }

        if ($this->containsDangerousContent($contents)) {
            throw new RuntimeException(__('This SVG was rejected because it contains unsafe content.'));
        }

        $sanitizer = new Sanitizer();
        $sanitizer->removeRemoteReferences(true);
        $sanitizer->removeXMLTag(true);

        $clean = $sanitizer->sanitize($contents);

        if ($clean === false || $clean === '' || ! $this->containsSvgElement($clean)) {
            throw new RuntimeException(__('This SVG file is invalid or malformed and cannot be uploaded.'));
        }

        if ($this->containsDangerousContent($clean) || $this->sanitizerFoundUnsafeContent($sanitizer)) {
            throw new RuntimeException(__('This SVG was rejected because it contains unsafe content.'));
        }

        return $clean;
    }

    /**
     * @throws RuntimeException
     */
    public function sanitizeUploadedFile(UploadedFile $file): string
    {
        $path = $file->getRealPath();

        if ($path === false || ! is_readable($path)) {
            throw new RuntimeException(__('Unable to read the uploaded SVG file.'));
        }

        $contents = file_get_contents($path);

        if ($contents === false) {
            throw new RuntimeException(__('Unable to read the uploaded SVG file.'));
        }

        return $this->sanitize($contents);
    }

    /**
     * Return an UploadedFile wrapping sanitized SVG contents so callers can
     * store it without ever persisting the original unsafe document.
     *
     * @throws RuntimeException
     */
    public function toSafeUploadedFile(UploadedFile $file): UploadedFile
    {
        $sanitized = $this->sanitizeUploadedFile($file);
        $tempPath = $this->writeTemporarySvg($sanitized);

        return new UploadedFile(
            $tempPath,
            $file->getClientOriginalName(),
            'image/svg+xml',
            UPLOAD_ERR_OK,
            true
        );
    }

    public function containsDangerousContent(string $svg): bool
    {
        $normalized = $this->normalizeForInspection($svg);

        if (preg_match('/<script\b/i', $normalized) === 1) {
            return true;
        }

        if (preg_match('/\son[a-z]+\s*=/i', $normalized) === 1) {
            return true;
        }

        if (preg_match('/javascript\s*:/i', $normalized) === 1) {
            return true;
        }

        if (preg_match('/<(foreignObject|object|embed|iframe|applet|form)\b/i', $normalized) === 1) {
            return true;
        }

        if (preg_match('/data\s*:\s*text\/html/i', $normalized) === 1) {
            return true;
        }

        return false;
    }

    protected function sanitizerFoundUnsafeContent(Sanitizer $sanitizer): bool
    {
        foreach ($sanitizer->getXmlIssues() as $issue) {
            $message = strtolower((string) ($issue['message'] ?? ''));

            if (preg_match("/suspicious (tag|attribute) '(script|foreignobject|object|embed|iframe|applet|form|on[a-z]+|href)'/i", $message) === 1) {
                return true;
            }
        }

        return false;
    }

    protected function containsSvgElement(string $contents): bool
    {
        return preg_match('/<svg\b/i', $contents) === 1;
    }

    protected function normalizeForInspection(string $svg): string
    {
        $withoutComments = preg_replace('/<!--.*?-->/s', '', $svg) ?? $svg;

        return html_entity_decode($withoutComments, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    protected function writeTemporarySvg(string $contents): string
    {
        $tempPath = tempnam(sys_get_temp_dir(), 'safe_svg_');

        if ($tempPath === false) {
            throw new RuntimeException(__('Unable to create a temporary file for the sanitized SVG.'));
        }

        $svgPath = $tempPath . '.svg';

        if (! rename($tempPath, $svgPath)) {
            @unlink($tempPath);
            throw new RuntimeException(__('Unable to create a temporary file for the sanitized SVG.'));
        }

        if (file_put_contents($svgPath, $contents) === false) {
            @unlink($svgPath);
            throw new RuntimeException(__('Unable to write the sanitized SVG.'));
        }

        return $svgPath;
    }
}
