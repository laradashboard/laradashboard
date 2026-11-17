<?php

declare(strict_types=1);

use App\Livewire\Datatable\PostDatatable;
use App\Models\Post;
use App\Models\Term;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function datatablePost(array $attrs = []): Post
{
    return Post::create($attrs + [
        'title' => 'A post', 'slug' => 'a-post-'.uniqid(), 'post_type' => 'post', 'status' => 'draft',
        'user_id' => User::factory()->create()->id, 'content' => '<p>Body</p>',
    ]);
}

test('titles and category names are escaped in the posts list', function () {
    $post = datatablePost(['title' => '<img src=x onerror=alert(1)>']);
    $term = Term::create(['name' => '<b>bold</b>', 'slug' => 'bold', 'taxonomy' => 'category']);
    $post->terms()->attach($term);

    $table = app(PostDatatable::class);

    expect($table->renderTitleColumn($post))->not->toContain('<img src=x')->toContain('&lt;img src=x')
        ->and($table->renderCategoryColumn($post->fresh()))->not->toContain('<b>bold</b>');
});

test('a scheduled post shows its compact publish time on one line', function () {
    $post = datatablePost(['status' => 'scheduled', 'published_at' => now()->setDateTime(2026, 11, 19, 9, 37)]);

    $html = app(PostDatatable::class)->renderStatusColumn($post);

    expect($html)->toContain('Nov 19, 9:37 AM')->toContain('whitespace-nowrap')->not->toContain('<br>');
});

test('table dates drop the year for this year and keep it otherwise', function () {
    $this->travelTo(now()->setDateTime(2026, 11, 16, 12, 0));
    $table = app(PostDatatable::class);

    expect($table->renderCreatedAtColumn(tap(datatablePost())->forceFill(['created_at' => now()->setDate(2026, 3, 4)])))->toContain('>04 Mar<')
        ->and($table->renderCreatedAtColumn(tap(datatablePost())->forceFill(['created_at' => now()->setDate(2025, 3, 4)])))->toContain('>04 Mar 2025<');
});
