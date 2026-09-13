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

test('datatable renders sticky header scroll container', function () {
    $this->actingAs($this->admin);

    Livewire::test(UserDatatable::class)
        ->assertSeeHtml('datatable-scroll-area')
        ->assertSeeHtml('table-thead-sticky');
});
