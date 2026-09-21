<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Mcp\Attributes\McpToolMeta;
use App\Mcp\Tools\Concerns\InteractsWithMcpAuthorization;
use App\Services\MediaLibraryService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;

#[Name('list-media')]
#[Description('Search the media library for images and files. Use before attach-featured-image to find media IDs or URLs.')]
#[McpToolMeta(ability: 'mcp:media.read', permission: 'media.view', group: 'Content')]
/** @phpstan-import-type McpFormattedMedia from \App\Services\MediaLibraryService */
class ListMediaTool extends Tool
{
    use InteractsWithMcpAuthorization;

    public function __construct(
        protected MediaLibraryService $mediaLibraryService,
    ) {
    }

    public function handle(Request $request): Response
    {
        if ($response = $this->authorizeMcpAbility('mcp:media.read', 'media.view')) {
            return $response;
        }

        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'in:images,videos,audio,documents'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        if (isset($validated['page'])) {
            request()->merge(['page' => $validated['page']]);
        }

        $result = $this->mediaLibraryService->getMediaList(
            search: $validated['search'] ?? null,
            type: $validated['type'] ?? null,
            perPage: (int) ($validated['per_page'] ?? 24),
        );

        $media = $result['media'];

        return Response::json([
            'data' => $media->getCollection()
                ->map(fn ($item): array => $this->mediaLibraryService->formatMediaForMcp($item))
                ->values()
                ->all(),
            'meta' => [
                'current_page' => $media->currentPage(),
                'last_page' => $media->lastPage(),
                'per_page' => $media->perPage(),
                'total' => $media->total(),
                'stats' => $result['stats'],
            ],
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'search' => $schema->string()
                ->description('Optional filter by file name or mime type.'),
            'type' => $schema->string()
                ->description('Optional media type filter.')
                ->enum(['images', 'videos', 'audio', 'documents']),
            'per_page' => $schema->integer()
                ->description('Number of records to return (max 50).')
                ->default(24),
            'page' => $schema->integer()
                ->description('Page number for pagination.')
                ->default(1),
        ];
    }
}
