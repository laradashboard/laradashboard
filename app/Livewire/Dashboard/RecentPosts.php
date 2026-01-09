<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard;

use App\Models\Post;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use Livewire\Component;

class RecentPosts extends Component
{
    public int $limit = 5;

    public function mount(int $limit = 5): void
    {
        $this->limit = $limit;
    }

    #[On('draft-created')]
    public function refreshPosts(): void
    {
        // This will trigger a re-render with fresh data
    }

    public function getPostsProperty(): Collection
    {
        return Post::query()
            ->with('user')
            ->where('post_type', 'post')
            ->latest()
            ->limit($this->limit)
            ->get();
    }

    public function getStatusColor(string $status): string
    {
        return match ($status) {
            'published' => '#22C55E',
            'draft' => '#F59E0B',
            'pending' => '#3B82F6',
            'scheduled' => '#8B5CF6',
            'private' => '#6B7280',
            default => '#6B7280',
        };
    }

    public function render()
    {
        return view('livewire.dashboard.recent-posts', [
            'posts' => $this->posts,
        ]);
    }
}
