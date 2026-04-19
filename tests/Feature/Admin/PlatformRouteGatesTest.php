<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Locks in the permission-based gating of platform-administration routes.
 *
 * A user WITHOUT the relevant permission (e.g. `settings.edit`) must
 * receive 403 on the gated routes, while a user WITH the permission
 * passes through to the controller/policy layer.
 *
 * This uses raw permissions, not role names, so the test stays green
 * no matter which role bundles the permission.
 */
class PlatformRouteGatesTest extends TestCase
{
    use RefreshDatabase;

    private function userWithPermissions(array $permissions = []): User
    {
        // Ensure the web guard exists for each permission.
        foreach ($permissions as $name) {
            Permission::findOrCreate($name, 'web');
        }

        $role = Role::findOrCreate('TestGated', 'web');
        $role->syncPermissions($permissions);

        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole($role);

        return $user;
    }

    #[Test]
    public function user_without_settings_edit_cannot_reach_settings_page(): void
    {
        $user = $this->userWithPermissions([]);

        $this->actingAs($user)
            ->get('/admin/settings')
            ->assertForbidden();
    }

    #[Test]
    public function user_with_settings_edit_reaches_settings_page(): void
    {
        $user = $this->userWithPermissions(['settings.view', 'settings.edit']);

        $response = $this->actingAs($user)->get('/admin/settings');

        // The route passes through the gate. Any 2xx/3xx from the
        // controller is fine — we're only asserting the gate opened.
        $this->assertNotSame(
            403,
            $response->status(),
            'can:settings.edit gate should allow a user with the permission'
        );
    }

    #[Test]
    public function user_without_role_view_cannot_reach_roles_index(): void
    {
        $user = $this->userWithPermissions([]);

        $this->actingAs($user)
            ->get('/admin/roles')
            ->assertForbidden();
    }

    #[Test]
    public function user_without_module_view_cannot_reach_modules_index(): void
    {
        $user = $this->userWithPermissions([]);

        $this->actingAs($user)
            ->get('/admin/modules')
            ->assertForbidden();
    }

    #[Test]
    public function user_without_user_view_cannot_reach_users_index(): void
    {
        $user = $this->userWithPermissions([]);

        $this->actingAs($user)
            ->get('/admin/users')
            ->assertForbidden();
    }

    #[Test]
    public function user_without_translations_view_cannot_reach_translations(): void
    {
        $user = $this->userWithPermissions([]);

        $this->actingAs($user)
            ->get('/admin/translations')
            ->assertForbidden();
    }

    #[Test]
    public function gated_routes_block_guests_via_auth_before_can(): void
    {
        // No actingAs — guest. Should redirect to login (302), not 403.
        $this->get('/admin/settings')->assertRedirect();
        $this->get('/admin/users')->assertRedirect();
    }
}
