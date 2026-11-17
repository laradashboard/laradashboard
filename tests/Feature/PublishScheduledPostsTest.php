<?php

declare(strict_types=1);

use App\Models\Post;
use App\Models\User;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function scheduledPost(string $status, $publishedAt): Post
{
    return Post::create([
        'title' => 'Post '.uniqid(), 'slug' => 'post-'.uniqid(), 'post_type' => 'post', 'status' => $status,
        'published_at' => $publishedAt, 'user_id' => User::factory()->create()->id, 'content' => '<p>Body</p>',
    ]);
}

test('scheduled posts go live once their publish date arrives', function () {
    $due = scheduledPost('scheduled', now()->subMinute());
    $later = scheduledPost('scheduled', now()->addDay());
    $draft = scheduledPost('draft', now()->subDay());

    $this->artisan('posts:publish-scheduled')->expectsOutput('Published 1 scheduled post.')->assertSuccessful();

    expect($due->fresh()->status)->toBe('published')
        ->and($later->fresh()->status)->toBe('scheduled')
        ->and($draft->fresh()->status)->toBe('draft');
});

test('the scheduler runs the publisher every minute', function () {
    $event = collect(app(Schedule::class)->events())->first(fn ($e) => str_contains($e->command ?? '', 'posts:publish-scheduled'));

    expect($event)->not->toBeNull()->and($event->expression)->toBe('* * * * *');
});
