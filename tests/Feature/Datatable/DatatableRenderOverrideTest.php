<?php

declare(strict_types=1);

use App\Http\Middleware\VerifyCsrfToken;
use App\Livewire\Datatable\RoleDatatable;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

/*
 * Modules extend the core datatable and sometimes override render(). Those overrides were
 * written before column visibility existed and don't pass $visibleHeaders, which used to
 * throw "Undefined variable $visibleHeaders" (seen in CustomForm's submissions table).
 */

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutMiddleware(VerifyCsrfToken::class);

    $role = Role::firstOrCreate(['name' => 'Superadmin', 'guard_name' => 'web']);
    foreach (['role.view', 'role.create', 'role.edit', 'role.delete'] as $permission) {
        Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
    }
    $role->syncPermissions(['role.view', 'role.create', 'role.edit', 'role.delete']);

    $this->admin = User::factory()->create();
    $this->admin->assignRole($role);
    $this->actingAs($this->admin);
});

test('a datatable whose render() passes the pre-1.4.5 variables still renders', function () {
    Livewire::test(LegacyRenderRoleDatatable::class)
        ->assertOk()
        ->assertSeeHtml('data-datatable-column-visibility');
});

test('datatableViewData() lets overrides pass their own data and keep column visibility', function () {
    Livewire::test(ViewDataRenderRoleDatatable::class)
        ->assertOk()
        ->assertSeeHtml('data-datatable-column-visibility')
        ->assertSet('renderedWithOwnData', true);
});

test('the base datatable still renders through datatableViewData()', function () {
    Livewire::test(RoleDatatable::class)
        ->assertOk()
        ->assertSeeHtml('data-datatable-column-visibility');
});

class LegacyRenderRoleDatatable extends RoleDatatable
{
    public function render(): Renderable
    {
        $this->headers = $this->getHeaders();
        $this->filters = $this->getFilters();

        return view('backend.livewire.datatable.datatable', [
            'headers' => $this->headers,
            'data' => $this->getData(),
            'perPage' => $this->perPage,
            'perPageOptions' => $this->perPageOptions,
        ]);
    }
}

class ViewDataRenderRoleDatatable extends RoleDatatable
{
    public bool $renderedWithOwnData = false;

    public function render(): Renderable
    {
        $data = $this->getData();
        $this->renderedWithOwnData = true;

        return view('backend.livewire.datatable.datatable', $this->datatableViewData(['data' => $data]));
    }
}
