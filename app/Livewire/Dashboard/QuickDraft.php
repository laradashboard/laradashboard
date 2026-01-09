<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard;

use App\Enums\PostStatus;
use App\Models\Post;
use App\Services\Builder\BlockService;
use Illuminate\Support\Str;
use Livewire\Attributes\Validate;
use Livewire\Component;

class QuickDraft extends Component
{
    #[Validate('required|min:3|max:255')]
    public string $title = '';

    #[Validate('nullable|max:1000')]
    public string $content = '';

    public bool $showSuccess = false;

    public function save(): void
    {
        $this->validate();

        $blockService = app(BlockService::class);

        // Build blocks array for LaraBuilder compatibility
        $blocks = [];

        // Add text block for content if provided
        if (! empty($this->content)) {
            $blocks[] = $blockService->text($this->content, 'left', '#666666', '16px');
        }

        // Generate HTML content from blocks
        $htmlContent = $blockService->parseBlocks($blocks);

        // Create design_json structure
        $designJson = [
            'blocks' => $blocks,
            'version' => 1,
        ];

        Post::create([
            'title' => $this->title,
            'slug' => Str::slug($this->title),
            'content' => $htmlContent,
            'design_json' => $designJson,
            'excerpt' => Str::limit(strip_tags($this->content), 150),
            'post_type' => 'post',
            'status' => PostStatus::DRAFT->value,
            'user_id' => auth()->id(),
        ]);

        $this->reset(['title', 'content']);
        $this->showSuccess = true;

        $this->dispatch('draft-created');
    }

    public function render()
    {
        return view('livewire.dashboard.quick-draft');
    }
}
