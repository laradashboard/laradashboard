<?php

declare(strict_types=1);

namespace App\Livewire\Pages;

use App\Models\Post;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

abstract class BasePostsArchive extends BaseFrontendPage
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $category = '';

    #[Url]
    public string $sort = 'latest';

    public array $categories = [];

    public ?Post $blogPage = null;

    public function mount(): void
    {
        $this->categories = $this->query()->getCategories();
        $this->blogPage = $this->query()->findBlogPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCategory(): void
    {
        $this->resetPage();
    }

    public function updatedSort(): void
    {
        $this->resetPage();
    }

    /**
     * Get paginated posts with applied filters.
     */
    public function getPostsProperty(): ?LengthAwarePaginator
    {
        if (! class_exists(Post::class)) {
            return null;
        }

        $query = $this->query()->publishedPostsQuery();
        $query = $this->query()->applySearchFilter($query, $this->search);
        $query = $this->query()->applyCategoryFilter($query, $this->category);
        $query = $this->query()->applySort($query, $this->sort);

        return $this->query()->paginatePosts($query);
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'category', 'sort']);
        $this->resetPage();
    }
}
