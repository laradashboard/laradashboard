<?php

use App\Services\PermissionService;
use Illuminate\Database\Migrations\Migration;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        PermissionService::syncPermissionsForRoles([
            [
                'group_name' => 'monitoring',
                'permissions' => ['actionlog.clean'],
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        PermissionService::removePermissions(['actionlog.clean']);
    }
};
