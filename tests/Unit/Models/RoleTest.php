<?php

namespace Tests\Unit\Models;

use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RoleTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_extends_spatie_role()
    {
        $role = new Role();
        $this->assertInstanceOf(\Spatie\Permission\Models\Role::class, $role);
    }

    #[Test]
    public function it_uses_query_builder_trait()
    {
        $role = new Role();
        $this->assertTrue(in_array('App\Traits\QueryBuilderTrait', class_uses_recursive($role)));
    }

    #[Test]
    public function it_has_searchable_columns()
    {
        $role = new Role();
        $reflection = new \ReflectionClass($role);
        $method = $reflection->getMethod('getSearchableColumns');
        $method->setAccessible(true);

        $this->assertEquals(['name'], $method->invoke($role));
    }

    #[Test]
    public function it_has_excluded_sort_columns()
    {
        $role = new Role();
        $reflection = new \ReflectionClass($role);
        $method = $reflection->getMethod('getExcludedSortColumns');
        $method->setAccessible(true);

        $this->assertEquals(['user_count'], $method->invoke($role));
    }

    #[Test]
    public function it_can_create_role_with_permissions()
    {
        // Create permissions
        $permission1 = \Spatie\Permission\Models\Permission::create(['name' => 'test.permission1']);
        $permission2 = \Spatie\Permission\Models\Permission::create(['name' => 'test.permission2']);

        // Create role with permissions
        $role = Role::create(['name' => 'test-role']);
        $role->syncPermissions([$permission1->id, $permission2->id]);

        // Assert role has permissions
        $this->assertTrue($role->hasPermissionTo('test.permission1', 'web'));
        $this->assertTrue($role->hasPermissionTo('test.permission2', 'web'));
    }

    #[Test]
    public function it_can_be_assigned_to_users()
    {
        $role = Role::create(['name' => 'test-role']);
        $user = \App\Models\User::factory()->create();

        $user->assignRole($role);

        $this->assertTrue($user->hasRole('test-role'));
    }
}
