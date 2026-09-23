<?php

declare(strict_types=1);

use App\Http\Middleware\VerifyCsrfToken;
use App\Livewire\Datatable\UserDatatable;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutMiddleware(VerifyCsrfToken::class);

    $adminRole = Role::firstOrCreate(['name' => 'Superadmin', 'guard_name' => 'web']);

    foreach (['user.view', 'user.create', 'user.edit', 'user.delete'] as $permission) {
        Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
    }

    $adminRole->syncPermissions(['user.view', 'user.create', 'user.edit', 'user.delete']);

    $this->admin = User::factory()->create();
    $this->admin->assignRole($adminRole);
});

test('datatable renders shift-click selection wiring', function () {
    $this->actingAs($this->admin);

    Livewire::test(UserDatatable::class)
        ->assertSeeHtml('toggleItem(')
        ->assertSeeHtml('lastClickedIndex')
        ->assertSeeHtml('syncSelectedItemsToLivewire');
});

test('datatable renders sticky header page-scroll container', function () {
    $this->actingAs($this->admin);

    Livewire::test(UserDatatable::class)
        ->assertSeeHtml('datatable-page-scroll')
        ->assertSeeHtml('table-thead-sticky')
        ->assertSeeHtml('datatable-pagination-sticky');
});

test('unified page scroll is the default for every datatable', function () {
    $this->actingAs($this->admin);

    $component = Livewire::test(UserDatatable::class);

    expect($component->instance()->usesUnifiedPageScroll())->toBeTrue();

    $component
        ->assertSeeHtml('setupUnifiedPageScroll')
        ->assertSeeHtml('findOrAdoptPageHeader')
        ->assertSeeHtml("createElement('div')")
        ->assertSeeHtml("className = 'datatable-unified-scroll-header'")
        ->assertSeeHtml('datatable-page-scroll')
        ->assertSeeHtml('datatable-pagination-sticky')
        ->assertDontSeeHtml('datatable-scroll-area')
        ->assertDontSeeHtml("header.classList.add");
});
