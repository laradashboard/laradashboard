<?php

declare(strict_types=1);

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\View;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\Permission;
use App\Models\Role;
use App\Services\RolesService;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    // Disable CSRF protection for tests.
    $this->withoutMiddleware(VerifyCsrfToken::class);

    // Create permissions
    Permission::firstOrCreate(['name' => 'role.view', 'group_name' => 'role']);
    Permission::firstOrCreate(['name' => 'role.create', 'group_name' => 'role']);
    Permission::firstOrCreate(['name' => 'role.edit', 'group_name' => 'role']);
    Permission::firstOrCreate(['name' => 'role.delete', 'group_name' => 'role']);

    // Create an admin user with full permissions
    $this->admin = User::factory()->create([
        'first_name' => 'Admin',
        'last_name' => 'User',
        'email' => 'admin@example.com',
        'username' => 'admin',
    ]);

    $adminRole = Role::firstOrCreate(['name' => 'Superadmin', 'guard_name' => 'web']);
    $adminRole->syncPermissions([
        'role.view',
        'role.create',
        'role.edit',
        'role.delete',
    ]);
    $this->admin->assignRole('Superadmin');

    // Create regular user with no permissions
    $this->regularUser = User::factory()->create([
        'first_name' => 'Regular',
        'last_name' => 'User',
        'email' => 'regular@example.com',
        'username' => 'regular',
    ]);

    // Setup view mocking for roles
    View::addNamespace('backend', resource_path('views/backend'));

    // Add mock for roles index view
    View::composer('backend.pages.roles.index', function ($view) {
        // Create a paginator with empty items
        $paginator = new LengthAwarePaginator(
            [], // Items
            0,  // Total
            10, // Per page
            1   // Current page
        );

        // Set path for proper link generation
        $paginator->setPath(request()->url());

        $view->with([
            'roles' => $paginator,
            'breadcrumbs' => [
                'title' => 'Roles',
            ],
        ]);
    });

    // Add mock for roles create view
    View::composer('backend.pages.roles.create', function ($view) {
        $view->with([
            'roleService' => app(RolesService::class),
            'all_permissions' => Permission::all(),
            'permission_groups' => Permission::groupBy('group_name')->get(),
            'breadcrumbs' => [
                'title' => 'Create Role',
                'items' => [],
            ],
        ]);
    });

    // Add mock for roles edit view
    View::composer('backend.pages.roles.edit', function ($view) {
        // The role will be provided by the controller
        // We just need to add the other required variables
        $view->with([
            'roleService' => app(RolesService::class),
            'all_permissions' => Permission::all(),
            'permission_groups' => Permission::groupBy('group_name')->get(),
            'breadcrumbs' => [
                'title' => 'Edit Role',
                'items' => [],
            ],
        ]);
    });
});

test('admin can view roles list', function () {
    $response = $this->actingAs($this->admin)
        ->get('/admin/roles');

    $response->assertStatus(200)
        ->assertViewIs('backend.pages.roles.index');
});

test('admin can create role', function () {
    $response = $this->actingAs($this->admin)
        ->get('/admin/roles/create');

    $response->assertStatus(200)
        ->assertViewIs('backend.pages.roles.create');

    // Test storing a new role
    $permissions = Permission::pluck('name')->toArray();

    $response = $this->actingAs($this->admin)
        ->post('/admin/roles', [
            'name' => 'TestEditor',
            'permissions' => $permissions,
        ]);

    $response->assertRedirect('/admin/roles');
    $this->assertDatabaseHas('roles', [
        'name' => 'TestEditor',
    ]);

    // Check if permissions were assigned to the role
    $role = Role::where('name', 'TestEditor')->first();
    foreach ($permissions as $permission) {
        expect($role->hasPermissionTo($permission))->toBeTrue();
    }
});

test('admin can update role', function () {
    // Create a role to update with initial permissions
    $role = Role::create(['name' => 'Tester']);
    $initialPermission = Permission::where('name', 'role.view')->first();
    $role->givePermissionTo($initialPermission);

    // Test the edit page.
    $response = $this->actingAs($this->admin)
        ->get("/admin/roles/{$role->id}/edit");

    $response->assertStatus(200)
        ->assertViewIs('backend.pages.roles.edit');

    // Test updating the role.
    $newPermissions = Permission::whereIn('name', ['role.view', 'role.create'])->pluck('name')->toArray();

    $response = $this->actingAs($this->admin)
        ->put("/admin/roles/{$role->id}", [
            'name' => 'Updated Tester',
            'permissions' => $newPermissions,
        ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('roles', [
        'id' => $role->id,
        'name' => 'Updated Tester',
    ]);

    // Refresh the role
    $updatedRole = Role::find($role->id);
    expect($updatedRole->hasPermissionTo('role.view'))->toBeTrue();
    expect($updatedRole->hasPermissionTo('role.create'))->toBeTrue();
});

test('admin can delete role', function () {
    // Create a role to delete
    $role = Role::create(['name' => 'ToDelete']);

    $response = $this->actingAs($this->admin)
        ->delete("/admin/roles/{$role->id}");

    $response->assertRedirect();
    $this->assertDatabaseMissing('roles', ['id' => $role->id]);
});

test('admin cannot delete superadmin role', function () {
    // Enable demo mode for this test (the code should check this)
    config(['app.demo_mode' => true]);

    // Get the Superadmin role
    $superadminRole = Role::where('name', 'Superadmin')->first();

    // The test expects this to fail with 403 because Superadmin can't be deleted.
    $response = $this->actingAs($this->admin)
        ->from('/admin/roles')
        ->delete("/admin/roles/{$superadminRole->id}");

    $response->assertStatus(403);

    // Confirm role still exists
    $this->assertDatabaseHas('roles', ['id' => $superadminRole->id]);

    // Reset config
    config(['app.demo_mode' => false]);
});

test('user without permission cannot manage roles', function () {
    // Test index (view) access
    $this->actingAs($this->regularUser)
        ->get('/admin/roles')
        ->assertStatus(403);

    // Test create access
    $this->actingAs($this->regularUser)
        ->get('/admin/roles/create')
        ->assertStatus(403);

    // Test store access
    $this->actingAs($this->regularUser)
        ->post('/admin/roles', [
            'name' => 'NewRole',
            'permissions' => ['role.view'],
        ])
        ->assertStatus(403);

    // Create a role for testing edit and delete
    $role = Role::create(['name' => 'TestRole']);

    // Test edit access
    $this->actingAs($this->regularUser)
        ->get("/admin/roles/{$role->id}/edit")
        ->assertStatus(403);

    // Test update access
    $this->actingAs($this->regularUser)
        ->put("/admin/roles/{$role->id}", [
            'name' => 'UpdatedRole',
            'permissions' => ['role.view'],
        ])
        ->assertStatus(403);

    // PUT requests return 403 for unauthorized access
    // Test delete access
    $this->actingAs($this->regularUser)
        ->delete("/admin/roles/{$role->id}")
        ->assertStatus(403);

    // DELETE requests return 403 for unauthorized access
    // Verify the role still exists
    $this->assertDatabaseHas('roles', ['id' => $role->id]);
});

test('validation works when creating role', function () {
    $response = $this->actingAs($this->admin)
        ->post('/admin/roles', [
            'name' => '', // Empty name should fail validation
            'permissions' => [], // Empty permissions should fail validation
        ]);

    $response->assertSessionHasErrors(['name', 'permissions']);
});

test('admin can bulk delete roles', function () {
    // Create multiple roles for testing bulk delete
    $role1 = Role::create(['name' => 'BulkDelete1']);
    $role2 = Role::create(['name' => 'BulkDelete2']);
    $role3 = Role::create(['name' => 'BulkDelete3']);

    $response = $this->actingAs($this->admin)
        ->delete('/admin/roles/delete/bulk-delete', [
            'ids' => [$role1->id, $role2->id, $role3->id],
        ]);

    $response->assertRedirect();
    $this->assertDatabaseMissing('roles', ['id' => $role1->id]);
    $this->assertDatabaseMissing('roles', ['id' => $role2->id]);
    $this->assertDatabaseMissing('roles', ['id' => $role3->id]);
});
