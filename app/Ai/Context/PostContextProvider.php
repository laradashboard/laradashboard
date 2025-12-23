<?php

declare(strict_types=1);

namespace App\Ai\Context;

use App\Ai\Contracts\AiContextProviderInterface;
use App\Models\Post;

/**
 * Provides post-related context to AI.
 */
class PostContextProvider implements AiContextProviderInterface
{
    public function key(): string
    {
        return 'posts';
    }

    public function context(): array
    {
        return [
            'default_status' => 'draft',
            'supported_statuses' => ['draft', 'pending', 'published'],
            'total_posts' => $this->getTotalPosts(),
            'recent_topics' => $this->getRecentTopics(),
        ];
    }

    private function getTotalPosts(): int
    {
        if (! class_exists(Post::class)) {
            return 0;
        }

        return Post::query()->count();
    }

    private function getRecentTopics(): array
    {
        if (! class_exists(Post::class)) {
            return [];
        }

        return Post::query()
            ->latest()
            ->limit(5)
            ->pluck('title')
            ->toArray();
    }
}
