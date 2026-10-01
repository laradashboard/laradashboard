<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        $tableNames = config('permission.table_names');
        $rolesTable = $tableNames['roles'] ?? 'roles';

        if (! Schema::hasTable($rolesTable)) {
            return;
        }

        if (! Schema::hasColumn($rolesTable, 'is_super_admin')) {
            Schema::table($rolesTable, function (Blueprint $table) {
                $table->boolean('is_super_admin')->default(false)->after('guard_name');
            });
        }

        DB::table($rolesTable)
            ->where('name', 'Superadmin')
            ->where('guard_name', 'web')
            ->update(['is_super_admin' => true]);
    }

    public function down(): void
    {
        $tableNames = config('permission.table_names');
        $rolesTable = $tableNames['roles'] ?? 'roles';

        if (! Schema::hasTable($rolesTable) || ! Schema::hasColumn($rolesTable, 'is_super_admin')) {
            return;
        }

        Schema::table($rolesTable, function (Blueprint $table) {
            $table->dropColumn('is_super_admin');
        });
    }
};
