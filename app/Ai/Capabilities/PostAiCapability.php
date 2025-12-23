<?php

declare(strict_types=1);

namespace App\Ai\Capabilities;

use App\Ai\Actions\CreatePostAction;
use App\Ai\Actions\GenerateSeoMetaAction;
use App\Ai\Contracts\AiCapabilityInterface;

/**
 * Post module AI capability.
 */
class PostAiCapability implements AiCapabilityInterface
{
    public function name(): string
    {
        return 'Post Management';
    }

    public function description(): string
    {
        return 'Create, edit, and optimize posts using AI assistance';
    }

    public function actions(): array
    {
        return [
            CreatePostAction::class,
            GenerateSeoMetaAction::class,
        ];
    }

    public function isEnabled(): bool
    {
        return class_exists(\App\Models\Post::class);
    }
}
