<?php

declare(strict_types=1);

use App\Models\User;
use App\Services\Content\ContentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    foreach (['post.view', 'post.create', 'post.edit', 'post.publish', 'ai_content.generate'] as $name) {
        Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
    }

    app(ContentService::class)->registerPostType([
        'name' => 'post',
        'label' => 'Posts',
        'label_singular' => 'Post',
        'taxonomies' => [],
    ]);
});

function builderUser(array $permissions): User
{
    $role = Role::create(['name' => 'role-'.uniqid(), 'guard_name' => 'web']);
    $role->givePermissionTo($permissions);
    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

function builderPostTypeModel(string $html): array
{
    preg_match('/data-post-type-model="([^"]*)"/', $html, $model);
    preg_match('/data-statuses="([^"]*)"/', $html, $statuses);
    preg_match('/data-can-use-ai="([^"]*)"/', $html, $ai);

    return [
        json_decode(html_entity_decode($model[1]), true),
        json_decode(html_entity_decode($statuses[1]), true),
        $ai[1],
    ];
}

test('a writer without post.publish is offered review, not publishing, in the post editor', function () {
    $writer = builderUser(['post.view', 'post.create', 'post.edit']);

    $html = $this->actingAs($writer)->get('/admin/posts/post/create')->assertOk()->getContent();
    [$model, $statuses, $ai] = builderPostTypeModel($html);

    expect($model['can_publish'])->toBeFalse()
        ->and($ai)->toBe('false')
        ->and(array_keys($statuses))->toContain('draft', 'pending')
        ->not->toContain('published')
        ->not->toContain('scheduled')
        ->not->toContain('private');
});

test('a publisher keeps every status and the publish button in the post editor', function () {
    $editor = builderUser(['post.view', 'post.create', 'post.edit', 'post.publish', 'ai_content.generate']);

    $html = $this->actingAs($editor)->get('/admin/posts/post/create')->assertOk()->getContent();
    [$model, $statuses, $ai] = builderPostTypeModel($html);

    expect($model['can_publish'])->toBeTrue()
        ->and($ai)->toBe('true')
        ->and(array_keys($statuses))->toContain('draft', 'pending', 'published', 'scheduled', 'private');
});

test('autogenerating a password also fills the confirmation field', function () {
    $html = view('components.inputs.password', ['name' => 'password', 'autogenerate' => true])->render();

    expect($html)->toContain('[name=&quot;password_confirmation&quot;]');
});
