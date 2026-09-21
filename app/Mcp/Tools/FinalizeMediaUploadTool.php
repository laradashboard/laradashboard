<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Mcp\Attributes\McpToolMeta;
use App\Mcp\Tools\Concerns\InteractsWithMcpAuthorization;
use App\Models\Media;
use App\Services\MediaLibraryService;
use App\Services\Mcp\McpMediaUploadService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Validation\ValidationException;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;

#[Name('finalize-media-upload')]
#[Description('Verify a completed multipart MCP upload and return media details including serve_ok. Call after POSTing the file to upload_url from create-media-upload.')]
#[McpToolMeta(ability: 'mcp:media.write', permission: 'media.create', group: 'Content')]
class FinalizeMediaUploadTool extends Tool
{
    use InteractsWithMcpAuthorization;

    public function __construct(
        protected McpMediaUploadService $mcpMediaUploadService,
        protected MediaLibraryService $mediaLibraryService,
    ) {
    }

    public function handle(Request $request): Response
    {
        if ($response = $this->authorizeMcpAbility('mcp:media.write', 'media.create')) {
            return $response;
        }

        $validated = $request->validate([
            'upload_token' => ['required', 'string', 'uuid'],
        ]);

        try {
            $mediaId = $this->mcpMediaUploadService->resolveCompletedMediaId((string) $validated['upload_token']);
        } catch (ValidationException $exception) {
            $message = collect($exception->errors())->flatten()->first();

            return Response::error(is_string($message) ? $message : __('Upload is not ready to finalize.'));
        }

        $media = Media::query()->find($mediaId);

        if ($media === null) {
            return Response::error(__('Uploaded media record was not found.'));
        }

        $formatted = $this->mediaLibraryService->formatMediaForMcp($media);

        if (! $formatted['serve_ok']) {
            return Response::error(__('Media exists but the public file is missing or not serveable.'));
        }

        return Response::json([
            'message' => __('Media upload finalized successfully.'),
            'media' => $formatted,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'upload_token' => $schema->string()
                ->description('Token returned by create-media-upload after the file has been POSTed to upload_url.')
                ->required(),
        ];
    }
}
