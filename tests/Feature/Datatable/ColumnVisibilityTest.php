<?php

declare(strict_types=1);

use App\Http\Middleware\VerifyCsrfToken;
use App\Livewire\Datatable\ActionLogDatatable;
use App\Livewire\Datatable\PermissionDatatable;
use App\Livewire\Datatable\PostDatatable;
use App\Livewire\Datatable\RoleDatatable;
use App\Livewire\Datatable\TermDatatable;
use App\Livewire\Datatable\UserDatatable;
use App\Models\Permission;
use App\Models\Post;
use App\Models\Role;
use App\Models\User;
use App\Services\Content\ContentService;
use App\Services\Content\PostType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutMiddleware(VerifyCsrfToken::class);

    $adminRole = Role::firstOrCreate(['name' => 'Superadmin', 'guard_name' => 'web']);

    foreach ([
        'post.view',
        'post.create',
        'post.edit',
        'post.delete',
        'user.view',
        'user.create',
        'user.edit',
        'user.delete',
        'role.view',
        'role.create',
        'role.edit',
        'role.delete',
        'term.view',
        'term.create',
        'term.edit',
        'term.delete',
    ] as $permission) {
        Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
    }

    $adminRole->syncPermissions([
        'post.view',
        'post.create',
        'post.edit',
        'post.delete',
        'user.view',
        'user.create',
        'user.edit',
        'user.delete',
        'role.view',
        'role.create',
        'role.edit',
        'role.delete',
        'term.view',
        'term.create',
        'term.edit',
        'term.delete',
    ]);

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

test('datatables show the columns visibility control by default', function (string $component, array $params) {
    $this->actingAs($this->admin);

    Livewire::test($component, $params)
        ->assertSeeHtml('data-datatable-column-visibility')
        ->assertSee(__('Columns'));
})->with([
    'posts' => [PostDatatable::class, ['postType' => PostType::POST]],
    'pages' => [PostDatatable::class, ['postType' => PostType::PAGE]],
    'users' => [UserDatatable::class, []],
    'roles' => [RoleDatatable::class, []],
    'permissions' => [PermissionDatatable::class, []],
    'terms' => [TermDatatable::class, ['taxonomy' => 'category']],
    'action logs' => [ActionLogDatatable::class, []],
]);

test('posts datatable lists every column in the visibility control', function () {
    $this->actingAs($this->admin);

    Post::factory()->create([
        'title' => 'Visible Column Post',
        'post_type' => PostType::POST,
        'user_id' => $this->admin->id,
    ]);

    Livewire::test(PostDatatable::class, ['postType' => PostType::POST])
        ->assertSeeHtml('data-datatable-column-visibility')
        ->assertSeeHtml('x-model="visible"')
        ->assertDontSeeHtml('wire:click.prevent')
        ->assertSeeHtml('data-column-id="title"')
        ->assertSeeHtml('data-column-id="author"')
        ->assertSeeHtml('data-column-id="status"')
        ->assertSeeHtml('data-column-id="category"')
        ->assertSeeHtml('data-column-id="created_at"')
        ->assertSeeHtml('data-column-id="updated_at"')
        ->assertSeeHtml('data-column-id="actions"');
});

test('hiding a post column removes it from the table header', function () {
    $this->actingAs($this->admin);

    Post::factory()->create([
        'title' => 'Column Toggle Post',
        'post_type' => PostType::POST,
        'user_id' => $this->admin->id,
    ]);

    Livewire::test(PostDatatable::class, ['postType' => PostType::POST])
        ->assertSeeHtml('data-column-id="author"')
        ->call('toggleColumnVisibility', 'author')
        ->assertDontSeeHtml('data-column-id="author"')
        ->assertSeeHtml('data-column-id="title"')
        ->assertSet('visibleColumnIds', function (array $ids): bool {
            return ! in_array('author', $ids, true) && in_array('title', $ids, true);
        });
});

test('hiding a user column removes it from the table header', function () {
    $this->actingAs($this->admin);

    Livewire::test(UserDatatable::class)
        ->assertSeeHtml('data-column-id="email"')
        ->call('toggleColumnVisibility', 'email')
        ->assertDontSeeHtml('data-column-id="email"')
        ->assertSeeHtml('data-column-id="name"');
});

test('the last visible post column cannot be hidden', function () {
    $this->actingAs($this->admin);

    $component = Livewire::test(PostDatatable::class, ['postType' => PostType::POST])
        ->call('setVisibleColumnIds', ['title']);

    $component
        ->assertSet('visibleColumnIds', ['title'])
        ->call('toggleColumnVisibility', 'title')
        ->assertSet('visibleColumnIds', ['title'])
        ->assertSeeHtml('data-column-id="title"');
});

test('restored column ids ignore unknown values and empty selections', function () {
    $this->actingAs($this->admin);

    Livewire::test(PostDatatable::class, ['postType' => PostType::POST])
        ->call('setVisibleColumnIds', ['title', 'not-a-column'])
        ->assertSet('visibleColumnIds', ['title'])
        ->call('setVisibleColumnIds', [])
        ->assertSet('visibleColumnIds', function (array $ids): bool {
            return in_array('title', $ids, true)
                && in_array('actions', $ids, true)
                && ! in_array('not-a-column', $ids, true);
        });
});
