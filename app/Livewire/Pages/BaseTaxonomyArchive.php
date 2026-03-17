<?php

declare(strict_types=1);

namespace App\Livewire\Pages;

use App\Models\Post;
use App\Models\Term;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\WithPagination;

abstract class BaseTaxonomyArchive extends BaseFrontendPage
{
    use WithPagination;

    /**
     * The taxonomy type (e.g. 'category', 'tag').
     * Override in subclass.
     */
    protected string $taxonomy = 'category';

    public ?Term $term = null;

    public function mount(string $slug): void
    {
        $this->term = $this->query()->findTermBySlug($slug, $this->taxonomy);
    }

    /**
     * Get paginated posts for this term.
     */
    public function getPostsProperty(): ?LengthAwarePaginator
    {
        if (! class_exists(Post::class)) {
            return null;
        }

        return $this->query()->paginatePosts(
            $this->query()->postsForTerm($this->term)
        );
    }
}
