<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class () extends Migration {
    public function up(): void
    {
        $role = Role::where('name', 'Superadmin')->first();

        if (! $role) {
            return;
        }

        $permissions = ['menu.view', 'menu.create', 'menu.edit', 'menu.delete'];

        foreach ($permissions as $permName) {
            $perm = Permission::firstOrCreate(
                ['name' => $permName, 'guard_name' => 'web'],
                ['group_name' => 'menu']
            );

            if (! $role->hasPermissionTo($perm)) {
                $role->givePermissionTo($perm);
            }
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        // Permissions remain — only the role assignment was the issue
    }
};
