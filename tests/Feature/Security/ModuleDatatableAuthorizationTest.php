<?php

declare(strict_types=1);

use App\Livewire\Datatable\ModuleDatatable;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    $this->setUpSecurityUsers();
    config([
        'app.demo_mode' => false,
        'laradashboard.updates.enabled' => false,
    ]);

    Permission::findOrCreate('module.delete', 'web');
    Permission::findOrCreate('module.edit', 'web');
    $this->adminUser->givePermissionTo(['module.delete', 'module.edit']);
});

test('admin cannot toggle module status via livewire', function () {
    Livewire::actingAs($this->adminUser)
        ->test(ModuleDatatable::class)
        ->call('toggleStatus', 'not-a-module')
        ->assertForbidden();
});

test('admin cannot delete a module via livewire', function () {
    Livewire::actingAs($this->adminUser)
        ->test(ModuleDatatable::class)
        ->call('deleteItem', 'not-a-module')
        ->assertForbidden();
});

test('admin cannot bulk change module status or delete modules via livewire', function (string $method) {
    Livewire::actingAs($this->adminUser)
        ->test(ModuleDatatable::class)
        ->set('selectedItems', ['not-a-module'])
        ->call($method)
        ->assertForbidden();
})->with([
    'bulk activate' => 'bulkActivate',
    'bulk deactivate' => 'bulkDeactivate',
    'bulk delete' => 'bulkDelete',
]);

test('admin cannot install a module update via livewire', function () {
    $component = Livewire::actingAs($this->adminUser)
        ->test(ModuleDatatable::class);

    config(['laradashboard.updates.enabled' => true]);

    $component->call('updateModule', 'not-a-module')
        ->assertForbidden();
});

test('superadmin can invoke module status and delete livewire actions', function (string $method, array $arguments) {
    $component = Livewire::actingAs($this->superadminUser)
        ->test(ModuleDatatable::class);

    if ($arguments === []) {
        $component->set('selectedItems', ['not-a-module']);
    }

    $component->call($method, ...$arguments)
        ->assertHasNoErrors();
})->with([
    'toggle' => ['toggleStatus', ['not-a-module']],
    'delete' => ['deleteItem', ['not-a-module']],
    'bulk activate' => ['bulkActivate', []],
    'bulk deactivate' => ['bulkDeactivate', []],
    'bulk delete' => ['bulkDelete', []],
]);
