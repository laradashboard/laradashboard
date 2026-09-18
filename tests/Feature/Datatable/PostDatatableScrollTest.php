<?php

declare(strict_types=1);

use App\Http\Middleware\VerifyCsrfToken;
use App\Livewire\Datatable\PostDatatable;
use App\Livewire\Datatable\UserDatatable;
use App\Models\Permission;
use App\Models\Post;
use App\Models\Role;
use App\Models\User;
use App\Services\Content\ContentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutMiddleware(VerifyCsrfToken::class);

    $adminRole = Role::firstOrCreate(['name' => 'Superadmin', 'guard_name' => 'web']);

    foreach (['post.view', 'post.create', 'post.edit', 'post.delete', 'user.view'] as $permission) {
        Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
    }

    $adminRole->syncPermissions(['post.view', 'post.create', 'post.edit', 'post.delete', 'user.view']);

    $this->admin = User::factory()->create();
    $this->admin->assignRole($adminRole);

    app(ContentService::class)->registerPostType([
        'name' => 'post',
        'label' => 'Posts',
        'label_singular' => 'Post',
        'taxonomies' => ['category', 'tag'],
    ]);
});

test('posts index uses a unified page-scroll header', function () {
    $this->actingAs($this->admin)
        ->get('/admin/posts/post?direction=desc')
        ->assertOk()
        ->assertSee('data-datatable-unified-scroll-header', false)
        ->assertSee('datatable-unified-scroll-header', false);
});

test('posts datatable uses page scroll instead of an inner row scroller', function () {
    Post::factory()->count(3)->create([
        'post_type' => 'post',
        'user_id' => $this->admin->id,
    ]);

    $this->actingAs($this->admin);

    Livewire::test(PostDatatable::class, ['postType' => 'post'])
        ->assertSeeHtml('datatable-page-scroll')
        ->assertSeeHtml('datatable-toolbar')
        ->assertSeeHtml('table-thead-sticky')
        ->assertSeeHtml('datatable-pagination-sticky')
        ->assertDontSeeHtml('datatable-scroll-area');
});

test('other datatables keep the nested sticky scroll container', function () {
    $this->actingAs($this->admin);

    Livewire::test(UserDatatable::class)
        ->assertSeeHtml('datatable-scroll-area')
        ->assertDontSeeHtml('datatable-page-scroll')
        ->assertDontSeeHtml('datatable-pagination-sticky');
});
