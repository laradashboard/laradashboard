<?php

declare(strict_types=1);

use App\Http\Middleware\VerifyCsrfToken;
use App\Livewire\Dashboard\QuickDraft;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutMiddleware(VerifyCsrfToken::class);

    // Create admin user with permissions
    $this->admin = User::factory()->create();
    $adminRole = Role::firstOrCreate(['name' => 'Superadmin', 'guard_name' => 'web']);

    // Create necessary permissions
    Permission::firstOrCreate(['name' => 'post.create', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'post.view', 'guard_name' => 'web']);

    $adminRole->syncPermissions(['post.create', 'post.view']);
    $this->admin->assignRole($adminRole);
});

it('renders quick draft component successfully', function () {
    $this->actingAs($this->admin);

    Livewire::test(QuickDraft::class)
        ->assertStatus(200)
        ->assertSee(__('Quick Draft'));
});

it('can save a draft post with title only', function () {
    $this->actingAs($this->admin);

    Livewire::test(QuickDraft::class)
        ->set('title', 'My Test Draft Post')
        ->call('save')
        ->assertSet('title', '')
        ->assertSet('showSuccess', true);

    $this->assertDatabaseHas('posts', [
        'title' => 'My Test Draft Post',
        'slug' => 'my-test-draft-post',
        'status' => 'draft',
        'post_type' => 'post',
        'user_id' => $this->admin->id,
    ]);
});

it('can save a draft post with title and content', function () {
    $this->actingAs($this->admin);

    Livewire::test(QuickDraft::class)
        ->set('title', 'My Draft With Content')
        ->set('content', 'This is the content of my draft post.')
        ->call('save')
        ->assertSet('title', '')
        ->assertSet('content', '')
        ->assertSet('showSuccess', true);

    // Content is now stored as HTML blocks for LaraBuilder compatibility
    $post = \App\Models\Post::where('title', 'My Draft With Content')->first();
    expect($post)->not->toBeNull();
    expect($post->status)->toBe('draft');
    expect($post->content)->toContain('This is the content of my draft post.');
    expect($post->design_json)->not->toBeNull();
    expect($post->design_json['blocks'])->toBeArray();
});

it('validates title is required', function () {
    $this->actingAs($this->admin);

    Livewire::test(QuickDraft::class)
        ->set('title', '')
        ->call('save')
        ->assertHasErrors(['title' => 'required']);

    $this->assertDatabaseMissing('posts', ['user_id' => $this->admin->id]);
});

it('validates title minimum length', function () {
    $this->actingAs($this->admin);

    Livewire::test(QuickDraft::class)
        ->set('title', 'ab')
        ->call('save')
        ->assertHasErrors(['title' => 'min']);
});

it('validates title maximum length', function () {
    $this->actingAs($this->admin);

    Livewire::test(QuickDraft::class)
        ->set('title', str_repeat('a', 256))
        ->call('save')
        ->assertHasErrors(['title' => 'max']);
});

it('validates content maximum length', function () {
    $this->actingAs($this->admin);

    Livewire::test(QuickDraft::class)
        ->set('title', 'Valid Title')
        ->set('content', str_repeat('a', 1001))
        ->call('save')
        ->assertHasErrors(['content' => 'max']);
});

it('dispatches draft-created event after saving', function () {
    $this->actingAs($this->admin);

    Livewire::test(QuickDraft::class)
        ->set('title', 'Event Test Draft')
        ->call('save')
        ->assertDispatched('draft-created');
});

it('resets form after successful save', function () {
    $this->actingAs($this->admin);

    Livewire::test(QuickDraft::class)
        ->set('title', 'Reset Test Draft')
        ->set('content', 'Some content here')
        ->call('save')
        ->assertSet('title', '')
        ->assertSet('content', '');
});
