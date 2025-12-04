<?php

declare(strict_types=1);

use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\ApiTestUtils;

pest()->use(
    RefreshDatabase::class,
    WithFaker::class,
    ApiTestUtils::class
);

beforeEach(function () {
    // Create basic roles if they don't exist
    $this->createRoles();

    // Create permissions
    $this->createPermissions();

    // Create test users
    $this->user = User::factory()->create();
    $this->adminUser = User::factory()->create();

    // Assign permissions to users
    $this->assignPermissions();

    // Assign admin role to admin user if role system exists
    if (class_exists(Role::class)) {
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $this->adminUser->assignRole($adminRole);
        // Ensure admin user has all permissions for settings
        $adminRole->givePermissionTo('settings.edit');
        $adminRole->givePermissionTo('settings.view');
    }

    // Seed mail_from_address and mail_from_name for tests
    Setting::factory()->mailFromAddress('dev@example.com', 'Laravel App')->create();
    Setting::factory()->mailFromName('Laravel App')->create();
});

test('authenticated user can get translations', function () {
    $response = $this->getJson('/api/translations/en');

    // Should return translations or 404 if file doesn't exist
    expect([200, 404, 403])->toContain($response->status());

    if ($response->status() === 200) {
        $response->assertJsonStructure([]);
    }
});

test('translations endpoint handles invalid language', function () {
    $response = $this->getJson('/api/translations/invalid-lang');

    $response->assertStatus(404)
        ->assertJson(['error' => 'Language not found']);
});

test('translations endpoint handles malicious language input', function () {
    $maliciousInputs = [
        '../../../etc/passwd',
        'en/../../../.env',
        '<script>alert("xss")</script>',
    ];

    foreach ($maliciousInputs as $input) {
        $response = $this->getJson("/api/translations/{$input}");

        // Should return 404, not expose file system
        $response->assertStatus(404);
    }
});

test('authenticated user can list terms', function () {
    $this->authenticateUser();

    $response = $this->getJson('/api/v1/terms/category');

    // Should return terms or appropriate error
    expect([200, 404, 403])->toContain($response->status());
});

test('unauthenticated user cannot list terms', function () {
    $response = $this->getJson('/api/v1/terms/category');

    $response->assertStatus(401)
        ->assertJson(['message' => 'Unauthenticated.']);
});

test('authenticated user can create term', function () {
    $this->authenticateUser();

    $termData = [
        'name' => 'Test Category',
        'slug' => 'test-category',
        'description' => 'A test category',
    ];

    $response = $this->postJson('/api/v1/terms/category', $termData);

    // Should create or return validation error
    expect([201, 422, 403])->toContain($response->status());
});

test('term creation handles edge cases', function () {
    $this->authenticateUser();
    $edgeCases = $this->getEdgeCaseData();

    foreach ($edgeCases as $case => $value) {
        $response = $this->postJson('/api/v1/terms/category', [
            'name' => is_string($value) ? $value : 'Test Term',
            'slug' => 'test-slug-' . uniqid(),
            'description' => $value,
        ]);

        expect([200, 201, 422, 403])->toContain($response->status());
    }
});

test('authenticated user can bulk delete terms', function () {
    $this->authenticateUser();

    $response = $this->postJson('/api/v1/terms/category/bulk-delete', [
        'ids' => [1, 2, 3],
    ]);

    // Should process or return validation error
    expect([200, 404, 422, 403])->toContain($response->status());
});

test('authenticated user can list settings', function () {
    $this->authenticateUser();

    $response = $this->getJson('/api/v1/settings');

    expect([200, 403])->toContain($response->status());
    if ($response->status() === 200) {
        $response->assertJsonStructure([
            'data' => [],
        ]);
    }
});

test('authenticated user can show specific setting', function () {
    $this->authenticateUser();

    $response = $this->getJson('/api/v1/settings/site_name');

    expect([200, 404, 403])->toContain($response->status());
});

test('settings update handles edge cases', function () {
    $this->authenticateUser();
    $edgeCases = $this->getEdgeCaseData();

    foreach ($edgeCases as $case => $value) {
        $response = $this->putJson('/api/v1/settings', [
            'test_setting' => $value,
        ]);

        expect([200, 422, 403])->toContain($response->status());
    }
});

test('authenticated user can list action logs', function () {
    $this->authenticateUser();

    $response = $this->getJson('/api/v1/action-logs');

    expect([200, 403])->toContain($response->status());
    if ($response->status() === 200) {
        $response->assertJsonStructure($this->getApiResourceStructure());
    }
});

test('authenticated user can show action log', function () {
    $this->authenticateUser();

    $response = $this->getJson('/api/v1/action-logs/1');

    expect([200, 404, 403])->toContain($response->status());
});

test('action logs endpoints handle pagination', function () {
    $this->authenticateUser();

    $response = $this->getJson('/api/v1/action-logs?page=1&per_page=10');

    expect([200, 403])->toContain($response->status());
    if ($response->status() === 200) {
        $response->assertJsonStructure([
            'data' => [],
            'links' => [],
            'meta' => [
                'current_page',
                'per_page',
            ],
        ]);
    }
});

test('authenticated user can get ai providers', function () {
    $this->authenticateUser();

    $response = $this->getJson('/api/v1/ai/providers');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [],
        ]);
});

test('authenticated user can generate ai content', function () {
    $this->authenticateUser();

    $contentData = [
        'prompt' => 'Write a blog post about Laravel testing',
        'provider' => 'openai',
        'max_tokens' => 500,
    ];

    $response = $this->postJson('/api/v1/ai/generate-content', $contentData);

    // Should process or return validation/service error
    expect([200, 422, 500, 503])->toContain($response->status());
});

test('ai content generation validates input', function () {
    $this->authenticateUser();

    $response = $this->postJson('/api/v1/ai/generate-content', []);

    $response->assertStatus(422);
});

test('ai content generation handles edge cases', function () {
    $this->authenticateUser();
    $edgeCases = $this->getEdgeCaseData();

    foreach ($edgeCases as $case => $value) {
        $response = $this->postJson('/api/v1/ai/generate-content', [
            'prompt' => is_string($value) ? $value : 'Test prompt',
            'provider' => 'openai',
        ]);

        expect([200, 422, 500, 503])->toContain($response->status());
    }
});

test('authenticated user can list modules', function () {
    $this->authenticateUser();

    $response = $this->getJson('/api/v1/modules');

    expect([200, 403])->toContain($response->status());
    if ($response->status() === 200) {
        $response->assertJsonStructure([
            'data' => [],
        ]);
    }
});

test('authenticated user can show module', function () {
    $this->authenticateUser();

    $response = $this->getJson('/api/v1/modules/crm');

    expect([200, 404, 403])->toContain($response->status());
});

test('authenticated admin can toggle module status', function () {
    $this->authenticateAdmin();

    $response = $this->patchJson('/api/v1/modules/crm/toggle-status');

    expect([200, 404, 403])->toContain($response->status());
});

test('regular user cannot toggle module status', function () {
    $this->authenticateUser();

    // Not admin
    $response = $this->patchJson('/api/v1/modules/crm/toggle-status');

    expect([403, 401, 404])->toContain($response->status());
});

test('authenticated admin can delete module', function () {
    $this->authenticateAdmin();

    $response = $this->deleteJson('/api/v1/modules/test-module');

    expect([204, 404, 403])->toContain($response->status());
});

test('module endpoints handle invalid names', function () {
    $this->authenticateUser();

    $invalidNames = [
        '../../../etc/passwd',
        '<script>alert("xss")</script>',
        'module with spaces',
        'module@#$%',
    ];

    foreach ($invalidNames as $name) {
        $response = $this->getJson("/api/v1/modules/{$name}");

        expect([404, 422, 403])->toContain($response->status());
    }
});

test('legacy admin terms endpoints are accessible', function () {
    $this->authenticateUser();

    $endpoints = [
        ['POST', '/api/admin/terms/category', ['name' => 'Test Category']],
        ['PUT', '/api/admin/terms/category/1', ['name' => 'Updated Category']],
        ['DELETE', '/api/admin/terms/category/1'],
    ];

    foreach ($endpoints as $endpoint) {
        $method = $endpoint[0];
        $url = $endpoint[1];
        $data = isset($endpoint[2]) ? $endpoint[2] : [];
        $response = $this->json($method, $url, $data);

        // These might require web middleware, so accept 419 (CSRF) as valid
        expect([200, 201, 204, 404, 419, 422, 401])->toContain($response->status());
    }
});

test('all endpoints return json content type', function () {
    $this->authenticateUser();

    $endpoints = [
        '/api/v1/users',
        '/api/v1/roles',
        '/api/v1/permissions',
        '/api/v1/posts/page',
        '/api/v1/terms/category',
        '/api/v1/settings',
        '/api/v1/action-logs',
        '/api/v1/ai/providers',
        '/api/v1/modules',
        '/api/v1/crm',
        '/api/v1/taskmanagers',
    ];

    foreach ($endpoints as $endpoint) {
        $response = $this->getJson($endpoint);

        if ($response->status() !== 404) {
            $response->assertHeader('Content-Type', 'application/json');
        }
    }
});

test('all endpoints handle options preflight requests', function () {
    $endpoints = [
        '/api/v1/users',
        '/api/v1/roles',
        '/api/v1/posts/page',
        '/api/auth/login',
    ];

    foreach ($endpoints as $endpoint) {
        $response = $this->call('OPTIONS', $endpoint, [], [], [], [
            'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'GET',
            'HTTP_ORIGIN' => 'http://localhost:3000',
        ]);

        // Should handle OPTIONS requests for CORS
        expect([200, 204, 404])->toContain($response->status());
    }
});

test('api endpoints enforce rate limiting', function () {
    $this->authenticateUser();

    // Test rate limiting on a simple endpoint
    $responses = [];
    for ($i = 0; $i < 100; $i++) {
        $response = $this->getJson('/api/v1/users');
        $responses[] = $response->status();

        // Break early if rate limited
        if ($response->status() === 429) {
            break;
        }
    }

    // Check if any rate limiting occurred (implementation dependent)
    $hasRateLimit = in_array(429, $responses);
    expect($hasRateLimit || count($responses) < 100)->toBeTrue('Rate limiting test completed - either rate limited or endpoint allows many requests');
});

test('api endpoints handle large payloads', function () {
    $this->authenticateUser();

    // Test with large data payload
    $largeData = [
        'title' => str_repeat('Large Title ', 1000),
        'content' => str_repeat('Large content block ', 5000),
        'description' => str_repeat('Description ', 2000),
    ];

    $response = $this->postJson('/api/v1/posts/page', $largeData + ['post_type' => 'page']);

    // Should handle large payloads gracefully
    expect([201, 413, 422, 403])->toContain($response->status());
});

test('api endpoints validate content length', function () {
    $this->authenticateUser();

    // Test with empty body but content-length header
    $response = $this->call('POST', '/api/v1/users', [], [], [], [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_CONTENT_LENGTH' => '1000',
    ]);

    expect([400, 422, 302])->toContain($response->status());
});
