<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

test('health endpoint returns ok status and version', function () {
    $response = $this->getJson(route('api.health'));

    $response->assertOk()
        ->assertJsonStructure([
            'status',
            'version',
            'release_date',
            'name',
            'demo_mode',
            'environment',
            'module_count',
            'timestamp',
        ])
        ->assertJson([
            'status' => 'ok',
        ]);
});
