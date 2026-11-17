<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\PostStatus;
use App\Models\Post;
use Illuminate\Console\Command;

class PublishScheduledPosts extends Command
{
    protected $signature = 'posts:publish-scheduled';

    protected $description = 'Publish scheduled posts and pages whose publish date has arrived';

    public function handle(): int
    {
        $published = 0;

        Post::query()
            ->where('status', PostStatus::SCHEDULED->value)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->orderBy('id')
            ->each(function (Post $post) use (&$published): void {
                // Saved one by one so observers and hooks run as if the editor published it.
                $post->status = PostStatus::PUBLISHED->value;
                $post->save();
                $published++;
            });

        $this->info("Published {$published} scheduled ".str('post')->plural($published).'.');

        return self::SUCCESS;
    }
}
