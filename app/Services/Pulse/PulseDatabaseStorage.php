<?php

namespace App\Services\Pulse;

use Laravel\Pulse\Storage\DatabaseStorage;

class PulseDatabaseStorage extends DatabaseStorage
{
    /**
     * MySQL 9.6+ no longer allows md5() in generated columns, so key hashes
     * must be computed in PHP for MySQL/MariaDB (same approach as SQLite).
     */
    protected function requiresManualKeyHash(): bool
    {
        return in_array($this->connection()->getDriverName(), ['sqlite', 'mysql', 'mariadb'], true);
    }
}
