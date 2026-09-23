<?php

declare(strict_types=1);

use App\Http\Middleware\VerifyCsrfToken;
use App\Livewire\Datatable\PostDatatable;
use App\Livewire\Datatable\RoleDatatable;
use App\Livewire\Datatable\UserDatatable;
use App\Models\Permission;
use App\Models\Post;
use App\Models\Role;
use App\Models\Taxonomy;
use App\Models\User;
use App\Services\Content\ContentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutMiddleware(VerifyCsrfToken::class);

    $adminRole = Role::firstOrCreate(['name' => 'Superadmin', 'guard_name' => 'web']);

    $permissions = [
        'post.view',
        'post.create',
        'post.edit',
        'post.delete',
        'term.view',
        'user.view',
        'role.view',
        'module.view',
        'settings.view',
        'settings.edit',
        'actionlog.view',
        'email_template.view',
    ];

    foreach ($permissions as $permission) {
        Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
    }

    $adminRole->syncPermissions($permissions);

    $this->admin = User::factory()->create([
        'email_verified_at' => now(),
    ]);
    $this->admin->assignRole($adminRole);

    $contentService = app(ContentService::class);
    $contentService->registerPostType([
        'name' => 'post',
        'label' => 'Posts',
        'label_singular' => 'Post',
        'taxonomies' => ['category', 'tag'],
    ]);
    $contentService->registerPostType([
        'name' => 'page',
        'label' => 'Pages',
        'label_singular' => 'Page',
        'has_archive' => false,
        'hierarchical' => true,
        'taxonomies' => [],
    ]);
    $contentService->registerTaxonomy([
        'name' => 'category',
        'label' => 'Categories',
        'label_singular' => 'Category',
        'hierarchical' => true,
    ], 'post');
    $contentService->registerTaxonomy([
        'name' => 'tag',
        'label' => 'Tags',
        'label_singular' => 'Tag',
        'hierarchical' => false,
    ], 'post');

    Taxonomy::firstOrCreate(
        ['name' => 'category'],
        [
            'label' => 'Categories',
            'label_singular' => 'Category',
            'hierarchical' => true,
            'show_in_menu' => true,
            'post_types' => ['post'],
        ]
    );
    Taxonomy::firstOrCreate(
        ['name' => 'tag'],
        [
            'label' => 'Tags',
            'label_singular' => 'Tag',
            'hierarchical' => false,
            'show_in_menu' => true,
            'post_types' => ['post'],
        ]
    );
});

test('datatable list pages still render without a layout unified-scroll flag', function (string $uri) {
    $this->actingAs($this->admin)
        ->get($uri)
        ->assertOk();
})->with([
    '/admin/posts/post',
    '/admin/posts/page',
    '/admin/users',
    '/admin/roles',
    '/admin/permissions',
    '/admin/modules',
    '/admin/action-log',
    '/admin/settings/notifications',
    '/admin/settings/email-templates',
    '/admin/settings/email-connections',
    '/admin/settings/inbound-email-connections',
    '/admin/terms/category',
    '/admin/terms/tag',
]);

test('list page breadcrumbs stay full width in the server markup', function () {
    $html = $this->actingAs($this->admin)
        ->get('/admin/posts/page')
        ->assertOk()
        ->getContent();

    expect($html)
        ->toContain('mb-6 w-full flex flex-nowrap items-center justify-between gap-3')
        ->not->toContain('class="datatable-unified-scroll-header');
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
        ->assertSeeHtml("className = 'datatable-unified-scroll-header'")
        ->assertDontSeeHtml('datatable-scroll-area');
});

test('other datatables use the same unified page scroll', function () {
    $this->actingAs($this->admin);

    Livewire::test(UserDatatable::class)
        ->assertSeeHtml('datatable-page-scroll')
        ->assertSeeHtml('setupUnifiedPageScroll')
        ->assertSeeHtml('datatable-pagination-sticky')
        ->assertDontSeeHtml('datatable-scroll-area');

    Livewire::test(RoleDatatable::class)
        ->assertSeeHtml('datatable-page-scroll')
        ->assertSeeHtml('setupUnifiedPageScroll')
        ->assertSeeHtml('datatable-pagination-sticky')
        ->assertDontSeeHtml('datatable-scroll-area');
});
