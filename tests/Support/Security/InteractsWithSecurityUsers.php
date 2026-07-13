<?php

declare(strict_types=1);

namespace Tests\Support\Security;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Role;
use App\Models\User;
use Spatie\Permission\Models\Permission;

trait InteractsWithSecurityUsers
{
    protected User $adminUser;

    protected User $superadminUser;

    protected function setUpSecurityUsers(): void
    {
        $this->withoutMiddleware(VerifyCsrfToken::class);

        Role::firstOrCreate(['name' => Role::SUPERADMIN, 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => Role::ADMIN, 'guard_name' => 'web']);

        $this->adminUser = $this->createUserWithRole(Role::ADMIN, [
            'user.view',
            'user.create',
            'user.edit',
            'user.login_as',
            'module.create',
            'module.view',
        ]);

        $this->superadminUser = $this->createUserWithRole(Role::SUPERADMIN);
    }

    protected function ensurePermission(string $name): Permission
    {
        return Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
    }

    /**
     * @param  array<int, string>  $permissions
     */
    protected function createUserWithRole(string $roleName, array $permissions = []): User
    {
        foreach ($permissions as $permission) {
            $this->ensurePermission($permission);
        }

        $user = User::factory()->create();
        $user->assignRole($roleName);

        if ($permissions !== []) {
            $user->syncPermissions($permissions);
        }

        return $user;
    }
}
