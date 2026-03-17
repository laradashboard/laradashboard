<?php

declare(strict_types=1);

namespace App\Livewire\Pages;

use App\Models\Post;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

abstract class BaseSearchPage extends BaseFrontendPage
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $query = '';

    public function updatedQuery(): void
    {
        $this->resetPage();
    }

    /**
     * Get paginated search results.
     */
    public function getResultsProperty(): ?LengthAwarePaginator
    {
        if (! class_exists(Post::class) || empty($this->query)) {
            return null;
        }

        return $this->query()->paginatePosts(
            $this->query()->searchPosts($this->query)
        );
    }

    public function clearSearch(): void
    {
        $this->query = '';
        $this->resetPage();
    }
}
