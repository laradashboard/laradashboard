<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Mcp\Attributes\McpToolMeta;
use App\Mcp\Tools\Concerns\InteractsWithMcpAuthorization;
use App\Services\MediaLibraryService;
use App\Services\PostService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;

#[Name('attach-featured-image')]
#[Description('Set the featured image on an existing post using a media library ID or URL. Use list-media to find available media.')]
#[McpToolMeta(ability: 'mcp:posts.update', permission: 'post.edit', group: 'Content')]
class AttachFeaturedImageTool extends Tool
{
    use InteractsWithMcpAuthorization;

    public function __construct(
        protected PostService $postService,
        protected MediaLibraryService $mediaLibraryService,
    ) {
    }

    public function handle(Request $request): Response
    {
        if ($response = $this->authorizeMcpAbility('mcp:posts.update', 'post.edit')) {
            return $response;
        }

        $validated = $request->validate([
            'post_id' => ['required', 'integer', 'min:1'],
            'post_type' => ['nullable', 'string', 'in:post,page'],
            'media_id' => ['nullable', 'integer', 'min:1', 'required_without:media_url'],
            'media_url' => ['nullable', 'string', 'max:2048', 'required_without:media_id'],
        ]);

        $postType = $validated['post_type'] ?? 'post';
        $mediaReference = isset($validated['media_id'])
            ? (string) $validated['media_id']
            : (string) $validated['media_url'];

        try {
            $post = $this->postService->getPostById((int) $validated['post_id'], $postType);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return Response::error(__('Post not found.'));
        }

        if ($post === null) {
            return Response::error(__('Post not found.'));
        }

        $media = $this->mediaLibraryService->associateExistingMedia($post, $mediaReference, 'featured');

        if ($media === null) {
            return Response::error(__('Media not found in the library.'));
        }

        $url = $this->mediaLibraryService->resolveMediaUrl($media) ?? '';

        return Response::json([
            'message' => __('Featured image attached successfully.'),
            'post' => [
                'id' => $post->id,
                'title' => $post->title,
            ],
            'media' => [
                'id' => $media->id,
                'name' => $media->name,
                'file_name' => $media->file_name,
                'mime_type' => $media->mime_type,
                'url' => $url,
            ],
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'post_id' => $schema->integer()
                ->description('The post ID to update.')
                ->required(),
            'post_type' => $schema->string()
                ->description('Content type.')
                ->enum(['post', 'page'])
                ->default('post'),
            'media_id' => $schema->integer()
                ->description('Media library ID (from list-media).'),
            'media_url' => $schema->string()
                ->description('Media library URL (alternative to media_id).'),
        ];
    }
}
