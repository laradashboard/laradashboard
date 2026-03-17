<?php

declare(strict_types=1);

namespace App\Livewire\Components;

use App\Models\Post;
use App\Services\FrontendQueryService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

abstract class BasePostsListing extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $category = '';

    #[Url]
    public string $sort = 'latest';

    public array $categories = [];

    // Configurable props from block
    public int $postsPerPage = 12;

    public int $columns = 3;

    public bool $showSearch = true;

    public bool $showCategoryFilter = true;

    public bool $showSort = true;

    public bool $showExcerpt = true;

    public bool $showImage = true;

    public bool $showDate = true;

    public string $categorySlug = '';

    public string $postRoute = '';

    public function mount(
        int $postsPerPage = 12,
        int $columns = 3,
        bool $showSearch = true,
        bool $showCategoryFilter = true,
        bool $showSort = true,
        bool $showExcerpt = true,
        bool $showImage = true,
        bool $showDate = true,
        string $categorySlug = '',
        string $postRoute = '',
    ): void {
        $this->postsPerPage = $postsPerPage;
        $this->columns = $columns;
        $this->showSearch = $showSearch;
        $this->showCategoryFilter = $showCategoryFilter;
        $this->showSort = $showSort;
        $this->showExcerpt = $showExcerpt;
        $this->showImage = $showImage;
        $this->showDate = $showDate;
        $this->categorySlug = $categorySlug;
        $this->postRoute = $postRoute;

        if ($this->showCategoryFilter) {
            $this->categories = $this->query()->getCategories();
        }

        if (! empty($this->categorySlug) && empty($this->category)) {
            $this->category = $this->categorySlug;
        }
    }

    protected function query(): FrontendQueryService
    {
        return app(FrontendQueryService::class);
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

    public function getPostsProperty(): ?LengthAwarePaginator
    {
        if (! class_exists(Post::class)) {
            return null;
        }

        $query = $this->query()->publishedPostsQuery();
        $query = $this->query()->applySearchFilter($query, $this->search);
        $query = $this->query()->applyCategoryFilter($query, $this->category);
        $query = $this->query()->applySort($query, $this->sort);

        return $this->query()->paginatePosts($query, $this->postsPerPage);
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'category', 'sort']);
        $this->resetPage();
    }
}
