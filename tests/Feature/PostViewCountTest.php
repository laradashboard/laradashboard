<?php

declare(strict_types=1);

use App\Http\Middleware\VerifyCsrfToken;
use App\Livewire\Datatable\PostDatatable;
use App\Models\Permission;
use App\Models\Post;
use App\Models\Role;
use App\Models\User;
use App\Services\Content\ContentService;
use App\Services\Content\PostType;
use App\Enums\PostStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutMiddleware(VerifyCsrfToken::class);

    $adminRole = Role::firstOrCreate(['name' => 'Superadmin', 'guard_name' => 'web']);

    foreach (['post.view', 'post.create', 'post.edit', 'post.delete'] as $permission) {
        Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
    }

    $adminRole->syncPermissions(['post.view', 'post.create', 'post.edit', 'post.delete']);

    $this->admin = User::factory()->create();
    $this->admin->assignRole($adminRole);

    $contentService = app(ContentService::class);
    $contentService->registerPostType([
        'name' => PostType::POST,
        'label' => 'Posts',
        'label_singular' => 'Post',
        'taxonomies' => ['category', 'tag'],
    ]);
    $contentService->registerPostType([
        'name' => PostType::PAGE,
        'label' => 'Pages',
        'label_singular' => 'Page',
        'taxonomies' => [],
    ]);
});

test('view count is stored in post_meta and formats for the admin table', function () {
    $post = Post::factory()->create([
        'status' => PostStatus::PUBLISHED->value,
        'post_type' => PostType::POST,
        'user_id' => $this->admin->id,
    ]);

    expect($post->view_count)->toBe(0)
        ->and($post->formattedViewCount())->toBe('—');

    $post->incrementViews();
    $post->incrementViews();

    expect($post->fresh()->view_count)->toBe(2)
        ->and($post->fresh()->getMeta(Post::VIEWS_META_KEY))->toBe('2');

    $post->setMeta(Post::VIEWS_META_KEY, '850');
    expect($post->fresh()->formattedViewCount())->toBe('850');

    $post->setMeta(Post::VIEWS_META_KEY, '2100');
    expect($post->fresh()->formattedViewCount())->toBe('2.1k');
});

test('unpublished posts do not record views', function () {
    $post = Post::factory()->create([
        'status' => PostStatus::DRAFT->value,
        'post_type' => PostType::POST,
        'user_id' => $this->admin->id,
    ]);

    $post->incrementViews();

    expect($post->fresh()->view_count)->toBe(0);
});

test('posts and pages datatables show the views column', function (string $postType) {
    $this->actingAs($this->admin);

    $viewed = Post::factory()->create([
        'title' => 'Viewed Item',
        'post_type' => $postType,
        'status' => PostStatus::PUBLISHED->value,
        'user_id' => $this->admin->id,
    ]);
    $viewed->setMeta(Post::VIEWS_META_KEY, '2100');

    Post::factory()->create([
        'title' => 'Unviewed Item',
        'post_type' => $postType,
        'status' => PostStatus::DRAFT->value,
        'user_id' => $this->admin->id,
    ]);

    Livewire::test(PostDatatable::class, ['postType' => $postType])
        ->assertSeeHtml('data-column-id="views"')
        ->assertSee('2.1k')
        ->assertSee('—');
})->with([
    'posts' => [PostType::POST],
    'pages' => [PostType::PAGE],
]);
