<?php

declare(strict_types=1);

namespace App\Ai\Actions;

use App\Ai\Contracts\AiActionInterface;
use App\Ai\Data\AiResult;
use App\Models\Post;
use Exception;
use Illuminate\Support\Facades\Http;

/**
 * AI Action to generate SEO meta data for a post.
 */
class GenerateSeoMetaAction implements AiActionInterface
{
    public static function name(): string
    {
        return 'posts.generate_seo';
    }

    public static function description(): string
    {
        return 'Generate SEO meta title and description for an existing post';
    }

    public static function payloadSchema(): array
    {
        return [
            'type' => 'object',
            'required' => ['post_id'],
            'properties' => [
                'post_id' => [
                    'type' => 'integer',
                    'description' => 'The ID of the post to generate SEO for',
                ],
            ],
        ];
    }

    public static function permission(): ?string
    {
        return 'post.edit';
    }

    public function handle(array $payload): AiResult
    {
        try {
            $postId = $payload['post_id'] ?? null;

            if (! $postId) {
                return AiResult::failed(__('Post ID is required.'));
            }

            $post = Post::find($postId);

            if (! $post) {
                return AiResult::failed(__('Post not found.'));
            }

            $meta = $this->generateSeoMeta($post);

            $post->update([
                'meta_title' => $meta['title'],
                'meta_description' => $meta['description'],
            ]);

            return AiResult::success(
                message: __('SEO meta generated successfully.'),
                data: [
                    'post_id' => $post->id,
                    'meta_title' => $meta['title'],
                    'meta_description' => $meta['description'],
                ],
                actions: [
                    __('Edit Post') => route('admin.posts.edit', ['postType' => $post->post_type, 'post' => $post->id]),
                ],
                completedSteps: [
                    __('Analyzed post content'),
                    __('Generated SEO meta title'),
                    __('Generated SEO meta description'),
                ]
            );
        } catch (Exception $e) {
            return AiResult::failed(__('Failed to generate SEO: :error', ['error' => $e->getMessage()]));
        }
    }

    /**
     * @return array{title: string, description: string}
     */
    private function generateSeoMeta(Post $post): array
    {
        $provider = config('settings.ai_default_provider', 'openai');
        $apiKey = match ($provider) {
            'openai' => config('settings.ai_openai_api_key') ?: config('ai.openai.api_key'),
            'claude' => config('settings.ai_claude_api_key') ?: config('ai.anthropic.api_key'),
            default => '',
        };

        if (empty($apiKey)) {
            return [
                'title' => substr($post->title, 0, 60),
                'description' => substr(strip_tags($post->content ?? $post->excerpt ?? ''), 0, 160),
            ];
        }

        $prompt = "Generate SEO meta for this post. Title: {$post->title}. Content: ".substr(strip_tags($post->content ?? ''), 0, 500);
        $systemPrompt = 'Generate SEO meta title (max 60 chars) and description (max 160 chars). Return JSON: {"title": "...", "description": "..."}';

        $response = match ($provider) {
            'openai' => Http::withHeaders([
                'Authorization' => 'Bearer '.$apiKey,
            ])->timeout(30)->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4o-mini',
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'max_tokens' => 200,
                'response_format' => ['type' => 'json_object'],
            ]),
            'claude' => Http::withHeaders([
                'x-api-key' => $apiKey,
                'anthropic-version' => '2023-06-01',
            ])->timeout(30)->post('https://api.anthropic.com/v1/messages', [
                'model' => 'claude-3-haiku-20240307',
                'max_tokens' => 200,
                'system' => $systemPrompt,
                'messages' => [['role' => 'user', 'content' => $prompt]],
            ]),
            default => null,
        };

        if (! $response || ! $response->successful()) {
            return [
                'title' => substr($post->title, 0, 60),
                'description' => substr(strip_tags($post->content ?? ''), 0, 160),
            ];
        }

        $content = match ($provider) {
            'openai' => $response->json('choices.0.message.content'),
            'claude' => $response->json('content.0.text'),
            default => '',
        };

        $data = json_decode($content, true) ?? [];

        return [
            'title' => $data['title'] ?? substr($post->title, 0, 60),
            'description' => $data['description'] ?? substr(strip_tags($post->content ?? ''), 0, 160),
        ];
    }
}
