<?php

declare(strict_types=1);

namespace App\Database\Concerns;

use Illuminate\Support\Facades\DB;

/**
 * Trait for toggling foreign key constraints in a database-agnostic way.
 *
 * Use this trait in migrations that need to temporarily disable foreign key
 * checks for seeding data during migration (e.g., when referencing users
 * that don't exist yet).
 */
trait TogglesForeignKeyConstraints
{
    /**
     * Disable foreign key checks in a database-agnostic way.
     */
    protected function disableForeignKeyChecks(): void
    {
        $driver = DB::getDriverName();

        match ($driver) {
            'sqlite' => DB::statement('PRAGMA foreign_keys = OFF'),
            'mysql', 'mariadb' => DB::statement('SET FOREIGN_KEY_CHECKS=0'),
            'pgsql' => DB::statement('SET session_replication_role = replica'),
            default => null,
        };
    }

    /**
     * Enable foreign key checks in a database-agnostic way.
     */
    protected function enableForeignKeyChecks(): void
    {
        $driver = DB::getDriverName();

        match ($driver) {
            'sqlite' => DB::statement('PRAGMA foreign_keys = ON'),
            'mysql', 'mariadb' => DB::statement('SET FOREIGN_KEY_CHECKS=1'),
            'pgsql' => DB::statement('SET session_replication_role = origin'),
            default => null,
        };
    }

    /**
     * Execute a callback with foreign key checks disabled.
     */
    protected function withoutForeignKeyChecks(callable $callback): mixed
    {
        $this->disableForeignKeyChecks();

        try {
            return $callback();
        } finally {
            $this->enableForeignKeyChecks();
        }
    }
}
