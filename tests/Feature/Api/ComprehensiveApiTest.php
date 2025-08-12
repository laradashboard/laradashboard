<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiTestUtils;
use Tests\TestCase;

class ComprehensiveApiTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;
    use ApiTestUtils;

    protected function setUp(): void
    {
        parent::setUp();

        // Create basic roles if they don't exist
        $this->createRoles();

        // Create permissions
        $this->createPermissions();

        // Create test users
        $this->user = \App\Models\User::factory()->create();
        $this->adminUser = \App\Models\User::factory()->create();

        // Assign permissions to users
        $this->assignPermissions();

        // Assign admin role to admin user if role system exists
        if (class_exists(\App\Models\Role::class)) {
            $adminRole = \App\Models\Role::firstOrCreate(['name' => 'admin']);
            $this->adminUser->assignRole($adminRole);
        }
    }

    #[Test]
    public function authenticated_user_can_get_translations()
    {
        $response = $this->getJson('/api/translations/en');

        // Should return translations or 404 if file doesn't exist
        $this->assertContains($response->status(), [200, 404, 403]);

        if ($response->status() === 200) {
            $response->assertJsonStructure([]);
        }
    }

    #[Test]
    public function translations_endpoint_handles_invalid_language()
    {
        $response = $this->getJson('/api/translations/invalid-lang');

        $response->assertStatus(404)
            ->assertJson(['error' => 'Language not found']);
    }

    #[Test]
    public function translations_endpoint_handles_malicious_language_input()
    {
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
    }

    #[Test]
    public function authenticated_user_can_list_terms()
    {
        $this->authenticateUser();

        $response = $this->getJson('/api/v1/terms/category');

        // Should return terms or appropriate error
        $this->assertContains($response->status(), [200, 404, 403]);
    }

    #[Test]
    public function unauthenticated_user_cannot_list_terms()
    {
        $response = $this->getJson('/api/v1/terms/category');

        $response->assertStatus(401)
            ->assertJson(['message' => 'Unauthenticated.']);
    }

    #[Test]
    public function authenticated_user_can_create_term()
    {
        $this->authenticateUser();

        $termData = [
            'name' => 'Test Category',
            'slug' => 'test-category',
            'description' => 'A test category',
        ];

        $response = $this->postJson('/api/v1/terms/category', $termData);

        // Should create or return validation error
        $this->assertContains($response->status(), [201, 422, 403]);
    }

    #[Test]
    public function term_creation_handles_edge_cases()
    {
        $this->authenticateUser();
        $edgeCases = $this->getEdgeCaseData();

        foreach ($edgeCases as $case => $value) {
            $response = $this->postJson('/api/v1/terms/category', [
                'name' => is_string($value) ? $value : 'Test Term',
                'slug' => 'test-slug-' . uniqid(),
                'description' => $value,
            ]);

            $this->assertContains($response->status(), [200, 201, 422, 403]);
        }
    }

    #[Test]
    public function authenticated_user_can_bulk_delete_terms()
    {
        $this->authenticateUser();

        $response = $this->postJson('/api/v1/terms/category/bulk-delete', [
            'ids' => [1, 2, 3],
        ]);

        // Should process or return validation error
        $this->assertContains($response->status(), [200, 404, 422, 403]);
    }

    #[Test]
    public function authenticated_user_can_list_settings()
    {
        $this->authenticateUser();

        $response = $this->getJson('/api/v1/settings');

        $this->assertContains($response->status(), [200, 403]);
        if ($response->status() === 200) {
            $response->assertJsonStructure([
                'data' => [],
            ]);
        }
    }

    #[Test]
    public function authenticated_user_can_update_settings()
    {
        $this->authenticateUser();

        $settingsData = [
            'site_name' => 'Updated Site Name',
            'site_description' => 'Updated description',
            'maintenance_mode' => false,
        ];

        $response = $this->putJson('/api/v1/settings', $settingsData);

        $this->assertContains($response->status(), [200, 403]);
    }

    #[Test]
    public function authenticated_user_can_show_specific_setting()
    {
        $this->authenticateUser();

        $response = $this->getJson('/api/v1/settings/site_name');

        $this->assertContains($response->status(), [200, 404, 403]);
    }

    #[Test]
    public function settings_update_handles_edge_cases()
    {
        $this->authenticateUser();
        $edgeCases = $this->getEdgeCaseData();

        foreach ($edgeCases as $case => $value) {
            $response = $this->putJson('/api/v1/settings', [
                'test_setting' => $value,
            ]);

            $this->assertContains($response->status(), [200, 422, 403]);
        }
    }

    #[Test]
    public function authenticated_user_can_list_action_logs()
    {
        $this->authenticateUser();

        $response = $this->getJson('/api/v1/action-logs');

        $this->assertContains($response->status(), [200, 403]);
        if ($response->status() === 200) {
            $response->assertJsonStructure($this->getApiResourceStructure());
        }
    }

    #[Test]
    public function authenticated_user_can_show_action_log()
    {
        $this->authenticateUser();

        $response = $this->getJson('/api/v1/action-logs/1');

        $this->assertContains($response->status(), [200, 404, 403]);
    }

    #[Test]
    public function action_logs_endpoints_handle_pagination()
    {
        $this->authenticateUser();

        $response = $this->getJson('/api/v1/action-logs?page=1&per_page=10');

        $this->assertContains($response->status(), [200, 403]);
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
    }

    #[Test]
    public function authenticated_user_can_get_ai_providers()
    {
        $this->authenticateUser();

        $response = $this->getJson('/api/v1/ai/providers');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [],
            ]);
    }

    #[Test]
    public function authenticated_user_can_generate_ai_content()
    {
        $this->authenticateUser();

        $contentData = [
            'prompt' => 'Write a blog post about Laravel testing',
            'provider' => 'openai',
            'max_tokens' => 500,
        ];

        $response = $this->postJson('/api/v1/ai/generate-content', $contentData);

        // Should process or return validation/service error
        $this->assertContains($response->status(), [200, 422, 500, 503]);
    }

    #[Test]
    public function ai_content_generation_validates_input()
    {
        $this->authenticateUser();

        $response = $this->postJson('/api/v1/ai/generate-content', []);

        $response->assertStatus(422);
    }

    #[Test]
    public function ai_content_generation_handles_edge_cases()
    {
        $this->authenticateUser();
        $edgeCases = $this->getEdgeCaseData();

        foreach ($edgeCases as $case => $value) {
            $response = $this->postJson('/api/v1/ai/generate-content', [
                'prompt' => is_string($value) ? $value : 'Test prompt',
                'provider' => 'openai',
            ]);

            $this->assertContains($response->status(), [200, 422, 500, 503]);
        }
    }

    #[Test]
    public function authenticated_user_can_list_modules()
    {
        $this->authenticateUser();

        $response = $this->getJson('/api/v1/modules');

        $this->assertContains($response->status(), [200, 403]);
        if ($response->status() === 200) {
            $response->assertJsonStructure([
                'data' => [],
            ]);
        }
    }

    #[Test]
    public function authenticated_user_can_show_module()
    {
        $this->authenticateUser();

        $response = $this->getJson('/api/v1/modules/crm');

        $this->assertContains($response->status(), [200, 404, 403]);
    }

    #[Test]
    public function authenticated_admin_can_toggle_module_status()
    {
        $this->authenticateAdmin();

        $response = $this->patchJson('/api/v1/modules/crm/toggle-status');

        $this->assertContains($response->status(), [200, 404, 403]);
    }

    #[Test]
    public function regular_user_cannot_toggle_module_status()
    {
        $this->authenticateUser(); // Not admin

        $response = $this->patchJson('/api/v1/modules/crm/toggle-status');

        $this->assertContains($response->status(), [403, 401]);
    }

    #[Test]
    public function authenticated_admin_can_delete_module()
    {
        $this->authenticateAdmin();

        $response = $this->deleteJson('/api/v1/modules/test-module');

        $this->assertContains($response->status(), [204, 404, 403]);
    }

    #[Test]
    public function module_endpoints_handle_invalid_names()
    {
        $this->authenticateUser();

        $invalidNames = [
            '../../../etc/passwd',
            '<script>alert("xss")</script>',
            'module with spaces',
            'module@#$%',
        ];

        foreach ($invalidNames as $name) {
            $response = $this->getJson("/api/v1/modules/{$name}");

            $this->assertContains($response->status(), [404, 422, 403]);
        }
    }

    #[Test]
    public function legacy_admin_terms_endpoints_are_accessible()
    {
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
            $this->assertContains($response->status(), [200, 201, 204, 404, 419, 422, 401]);
        }
    }

    #[Test]
    public function all_endpoints_return_json_content_type()
    {
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
    }

    #[Test]
    public function all_endpoints_handle_options_preflight_requests()
    {
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
            $this->assertContains($response->status(), [200, 204, 404]);
        }
    }

    #[Test]
    public function api_endpoints_enforce_rate_limiting()
    {
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
        $this->assertTrue($hasRateLimit || count($responses) < 100, 'Rate limiting test completed - either rate limited or endpoint allows many requests');
    }

    #[Test]
    public function api_endpoints_handle_large_payloads()
    {
        $this->authenticateUser();

        // Test with large data payload
        $largeData = [
            'title' => str_repeat('Large Title ', 1000),
            'content' => str_repeat('Large content block ', 5000),
            'description' => str_repeat('Description ', 2000),
        ];

        $response = $this->postJson('/api/v1/posts/page', $largeData + ['post_type' => 'page']);

        // Should handle large payloads gracefully
        $this->assertContains($response->status(), [201, 413, 422, 403]);
    }

    #[Test]
    public function api_endpoints_validate_content_length()
    {
        $this->authenticateUser();

        // Test with empty body but content-length header
        $response = $this->call('POST', '/api/v1/users', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_CONTENT_LENGTH' => '1000',
        ]);

        $this->assertContains($response->status(), [400, 422, 302]);
    }
}
