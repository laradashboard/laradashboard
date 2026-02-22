<?php

use App\Services\PermissionService;
use Illuminate\Database\Migrations\Migration;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add menu permissions and assign to Superadmin
        PermissionService::syncPermissionsForRoles([
            [
                'group_name' => 'menu',
                'permissions' => [
                    'menu.view',
                    'menu.create',
                    'menu.edit',
                    'menu.delete',
                ],
            ],
        ], ['Superadmin']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove menu permissions
        PermissionService::removePermissions([
            'menu.view',
            'menu.create',
            'menu.edit',
            'menu.delete',
        ]);
    }
};
