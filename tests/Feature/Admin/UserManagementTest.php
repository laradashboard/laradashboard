<?php

namespace Tests\Feature\Admin;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Create admin user with permissions
        $this->admin = User::factory()->create();
        $adminRole = Role::create(['name' => 'Superadmin', 'guard_name' => 'web']);

        // Create necessary permissions
        Permission::create(['name' => 'user.view']);
        Permission::create(['name' => 'user.create']);
        Permission::create(['name' => 'user.edit']);
        Permission::create(['name' => 'user.delete']);

        $adminRole->syncPermissions([
            'user.view',
            'user.create',
            'user.edit',
            'user.delete',
        ]);

        $this->admin->assignRole($adminRole);
    }

    #[Test]
    public function admin_can_view_users_list(): void
    {
        $this->actingAs($this->admin)
            ->get('/admin/users')
            ->assertStatus(200)
            ->assertViewIs('backend.pages.users.index');
    }

    #[Test]
    public function admin_can_create_user(): void
    {
        $role = Role::create(['name' => 'editor']);

        $response = $this->actingAs($this->admin)
            ->post('/admin/users', [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john@example.com',
                'username' => 'johndoe',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'roles' => ['editor'],
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'username' => 'johndoe',
        ]);

        $user = User::where('email', 'john@example.com')->first();
        $this->assertTrue($user->hasRole('editor'));
    }

    #[Test]
    public function admin_can_update_user(): void
    {
        $user = User::create([
            'first_name' => 'Original',
            'last_name' => 'Name',
            'email' => 'original@example.com',
            'username' => 'originaluser',
            'password' => Hash::make('password'),
        ]);

        $role = Role::create(['name' => 'editor']);

        $response = $this->actingAs($this->admin)
            ->put("/admin/users/{$user->id}", [
                'first_name' => 'Updated',
                'last_name' => 'Name',
                'email' => 'updated@example.com',
                'username' => 'updateduser',
                'roles' => ['editor'],
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'first_name' => 'Updated',
            'last_name' => 'Name',
            'email' => 'updated@example.com',
            'username' => 'updateduser',
        ]);

        $updatedUser = User::find($user->id);
        $this->assertTrue($updatedUser->hasRole('editor'));
    }

    #[Test]
    public function admin_can_delete_user(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($this->admin)
            ->delete("/admin/users/{$user->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    #[Test]
    public function admin_cannot_delete_themselves(): void
    {
        $response = $this->actingAs($this->admin)
            ->delete("/admin/users/{$this->admin->id}");

        $response->assertRedirect();
        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);
    }

    #[Test]
    public function user_without_permission_cannot_manage_users(): void
    {
        $regularUser = User::factory()->create();

        $this->actingAs($regularUser)
            ->get('/admin/users')
            ->assertStatus(403);

        $this->actingAs($regularUser)
            ->post('/admin/users', [
                'first_name' => 'New',
                'last_name' => 'User',
                'username' => 'newuser',
                'email' => 'newuser@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ])
            ->assertStatus(403);
    }

    #[Test]
    public function validation_works_when_creating_user(): void
    {
        $response = $this->actingAs($this->admin)
            ->post('/admin/users', [
                'first_name' => '',
                'last_name' => '',
                'email' => '',
                'password' => '',
            ]);

        $response->assertSessionHasErrors(['first_name', 'last_name', 'email', 'password']);
    }
}
