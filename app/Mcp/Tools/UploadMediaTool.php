<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Mcp\Attributes\McpToolMeta;
use App\Mcp\Tools\Concerns\InteractsWithMcpAuthorization;
use App\Services\MediaLibraryService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Validation\ValidationException;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;

#[Name('upload-media')]
#[Description('Upload a small image via base64 (max ~100KB decoded). For hero images and larger files, use create-media-upload with multipart POST instead.')]
#[McpToolMeta(ability: 'mcp:media.write', permission: 'media.create', group: 'Content')]
class UploadMediaTool extends Tool
{
    use InteractsWithMcpAuthorization;

    public function __construct(
        protected MediaLibraryService $mediaLibraryService,
    ) {
    }

    public function handle(Request $request): Response
    {
        if ($response = $this->authorizeMcpAbility('mcp:media.write', 'media.create')) {
            return $response;
        }

        $validated = $request->validate([
            'filename' => ['required', 'string', 'max:255'],
            'mime_type' => ['required', 'string', 'max:100'],
            'content_base64' => ['required', 'string'],
            'title' => ['nullable', 'string', 'max:255'],
            'alt_text' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $media = $this->mediaLibraryService->uploadFromBase64(
                filename: (string) $validated['filename'],
                mimeType: (string) $validated['mime_type'],
                contentBase64: (string) $validated['content_base64'],
                title: $validated['title'] ?? null,
                altText: $validated['alt_text'] ?? null,
            );
        } catch (ValidationException $exception) {
            $message = collect($exception->errors())->flatten()->first();

            return Response::error(is_string($message) ? $message : __('Upload validation failed.'));
        } catch (\Throwable $exception) {
            return Response::error(__('Media upload failed: :error', ['error' => $exception->getMessage()]));
        }

        $mediaPayload = $this->mediaLibraryService->formatMediaForMcp($media, probeHttp: true);

        if (! $mediaPayload['serve_ok']) {
            return Response::error(__('Media was stored but the public file is not serveable.'));
        }

        return Response::json([
            'message' => __('Media uploaded successfully.'),
            'media' => $mediaPayload,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'filename' => $schema->string()
                ->description('Safe file name including extension (e.g. hero.png).')
                ->required(),
            'mime_type' => $schema->string()
                ->description('Image MIME type (image/png, image/jpeg, image/webp, image/gif, image/svg+xml).')
                ->required(),
            'content_base64' => $schema->string()
                ->description('Base64-encoded file bytes (max ~100 KB decoded; use create-media-upload for larger heroes).')
                ->required(),
            'title' => $schema->string()
                ->description('Optional media library title.'),
            'alt_text' => $schema->string()
                ->description('Optional alt text stored on the media record.'),
        ];
    }
}
