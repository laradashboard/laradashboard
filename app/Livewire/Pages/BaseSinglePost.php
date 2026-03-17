<?php

declare(strict_types=1);

namespace App\Livewire\Pages;

use App\Models\Post;
use Illuminate\Support\Collection;

abstract class BaseSinglePost extends BaseFrontendPage
{
    public ?Post $post = null;

    public Collection|array $relatedPosts = [];

    public ?Post $previousPost = null;

    public ?Post $nextPost = null;

    public function mount(string $slug): void
    {
        $this->post = $this->query()->findPublishedPostBySlug($slug);

        $this->relatedPosts = $this->query()->relatedPosts($this->post);

        $adjacent = $this->query()->adjacentPosts($this->post);
        $this->previousPost = $adjacent['previous'];
        $this->nextPost = $adjacent['next'];
    }
}
