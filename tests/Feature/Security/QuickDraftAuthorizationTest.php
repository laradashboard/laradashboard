<?php

declare(strict_types=1);

use App\Livewire\Dashboard\QuickDraft;
use App\Models\Permission;
use App\Models\Post;
use App\Models\User;
use Livewire\Livewire;

/*
 * GHSA-p4mj-68rh-c24q: the dashboard Quick Draft widget saved posts without checking
 * post.create, so any logged-in user (e.g. a self-registered Subscriber) could create one.
 */

beforeEach(function () {
    foreach (['dashboard.view', 'post.view', 'post.create'] as $permission) {
        Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
    }
});

test('a user without post.create cannot save a quick draft', function () {
    $subscriber = User::factory()->create();
    $subscriber->givePermissionTo(['dashboard.view', 'post.view']);

    Livewire::actingAs($subscriber)
        ->test(QuickDraft::class)
        ->assertForbidden();

    expect(Post::count())->toBe(0);
});

test('a user with post.create can still save a quick draft', function () {
    $editor = User::factory()->create();
    $editor->givePermissionTo(['dashboard.view', 'post.create']);

    Livewire::actingAs($editor)
        ->test(QuickDraft::class)
        ->set('title', 'An idea worth writing')
        ->set('content', 'Notes')
        ->call('save')
        ->assertHasNoErrors()
        ->assertSet('showSuccess', true);

    expect(Post::where('title', 'An idea worth writing')->where('user_id', $editor->id)->exists())->toBeTrue();
});

test('losing post.create after the widget loaded still blocks the save', function () {
    $editor = User::factory()->create();
    $editor->givePermissionTo(['dashboard.view', 'post.create']);

    $widget = Livewire::actingAs($editor)->test(QuickDraft::class)->set('title', 'Too late');

    $editor->revokePermissionTo('post.create');
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

    $widget->call('save')->assertForbidden();

    expect(Post::count())->toBe(0);
});

test('the dashboard hides the quick draft widget from users who cannot create posts', function () {
    $subscriber = User::factory()->create();
    $subscriber->givePermissionTo(['dashboard.view', 'post.view']);

    $this->actingAs($subscriber)->get('/admin')->assertOk()->assertDontSeeLivewire(QuickDraft::class);
});
