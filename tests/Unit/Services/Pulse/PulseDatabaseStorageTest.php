<?php

namespace Tests\Unit\Services\Pulse;

use App\Services\Pulse\PulseDatabaseStorage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Laravel\Pulse\Storage\DatabaseStorage;
use Tests\TestCase;

class PulseDatabaseStorageTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_extends_pulse_database_storage(): void
    {
        $this->assertInstanceOf(DatabaseStorage::class, app(PulseDatabaseStorage::class));
    }

    public function test_it_requires_manual_key_hash_for_mysql(): void
    {
        Config::set('pulse.storage.database.connection', null);

        $storage = app(PulseDatabaseStorage::class);
        $method = new \ReflectionMethod(PulseDatabaseStorage::class, 'requiresManualKeyHash');

        $this->assertTrue($method->invoke($storage));
    }
}
