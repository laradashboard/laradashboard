<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Mcp\Attributes\McpToolMeta;
use App\Mcp\Tools\Concerns\InteractsWithMcpAuthorization;
use App\Services\Mcp\McpMediaUploadService;
use App\Services\MediaLibraryService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;

#[Name('create-media-upload')]
#[Description('Start a multipart media upload for hero images and large files. POST the file to upload_url (signed) or upload_url_bearer with Authorization, then call finalize-media-upload.')]
#[McpToolMeta(ability: 'mcp:media.write', permission: 'media.create', group: 'Content')]
class CreateMediaUploadTool extends Tool
{
    use InteractsWithMcpAuthorization;

    public function __construct(
        protected McpMediaUploadService $mcpMediaUploadService,
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
            'title' => ['nullable', 'string', 'max:255'],
            'alt_text' => ['nullable', 'string', 'max:500'],
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        try {
            $session = $this->mcpMediaUploadService->createPendingUpload($user, $validated);
        } catch (ValidationException $exception) {
            $message = collect($exception->errors())->flatten()->first();

            return Response::error(is_string($message) ? $message : __('Unable to create upload session.'));
        }

        return Response::json([
            'message' => __('Upload session created. POST multipart file field ":field" to upload_url before it expires.', [
                'field' => $session['upload_field'],
            ]),
            'upload' => $session,
            'max_bytes' => MediaLibraryService::MCP_MAX_UPLOAD_BYTES,
            'base64_fallback_max_bytes' => MediaLibraryService::MCP_BASE64_FALLBACK_MAX_BYTES,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'filename' => $schema->string()
                ->description('Safe file name including extension (e.g. hero.jpg).')
                ->required(),
            'mime_type' => $schema->string()
                ->description('Image MIME type (image/jpeg, image/png, image/webp, etc.).')
                ->required(),
            'title' => $schema->string()
                ->description('Optional media library title.'),
            'alt_text' => $schema->string()
                ->description('Optional alt text stored on the media record.'),
        ];
    }
}
