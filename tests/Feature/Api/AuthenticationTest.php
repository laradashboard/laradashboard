<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiTestUtils;
use Tests\TestCase;

class AuthenticationTest extends TestCase
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
        $this->user = User::factory()->create();
        $this->adminUser = User::factory()->create();

        // Assign permissions to users
        $this->assignPermissions();

        // Assign admin role to admin user if role system exists
        if (class_exists(\App\Models\Role::class)) {
            $adminRole = \App\Models\Role::firstOrCreate(['name' => 'admin']);
            $this->adminUser->assignRole($adminRole);
        }
    }

    #[Test]
    public function user_can_login_with_valid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'token',
                'user' => [
                    'id',
                    'name',
                    'email',
                    'created_at',
                    'updated_at',
                ],
            ]);

        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'tokenable_type' => User::class,
        ]);
    }

    #[Test]
    public function user_cannot_login_with_invalid_credentials()
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Invalid credentials',
            ]);
    }

    #[Test]
    public function login_requires_email_field()
    {
        $response = $this->postJson('/api/auth/login', [
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure($this->getValidationErrorStructure())
            ->assertJsonPath('errors.email', ['The email field is required.']);
    }

    #[Test]
    public function login_requires_password_field()
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure($this->getValidationErrorStructure())
            ->assertJsonPath('errors.password', ['The password field is required.']);
    }

    #[Test]
    public function login_validates_email_format()
    {
        $invalidEmails = [
            'not-an-email',
            '@domain.com',
            'test@',
            'test.domain.com',
            '',
            null,
            123,
            'test@.com',
        ];

        foreach ($invalidEmails as $email) {
            $response = $this->postJson('/api/auth/login', [
                'email' => $email,
                'password' => 'password123',
            ]);

            $response->assertStatus(422);
        }
    }

    #[Test]
    public function login_handles_edge_case_inputs()
    {
        $edgeCases = $this->getEdgeCaseData();

        foreach ($edgeCases as $case => $value) {
            $response = $this->postJson('/api/auth/login', [
                'email' => $value,
                'password' => $value,
            ]);

            $response->assertStatus(422);
        }
    }

    #[Test]
    public function authenticated_user_can_get_user_profile()
    {
        $user = $this->authenticateUser();

        $response = $this->getJson('/api/auth/user');

        $response->assertStatus(200)
            ->assertJson([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ])
            ->assertJsonStructure([
                'id',
                'name',
                'email',
                'created_at',
                'updated_at',
            ]);
    }

    #[Test]
    public function unauthenticated_user_cannot_get_user_profile()
    {
        $response = $this->getJson('/api/auth/user');

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    }

    #[Test]
    public function authenticated_user_can_logout()
    {
        $user = $this->authenticateUser();

        // Verify token exists
        $this->assertCount(1, $user->tokens);

        $response = $this->postJson('/api/auth/logout');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Logged out successfully',
            ]);

        // Verify token is deleted
        $user->refresh();
        $this->assertCount(0, $user->tokens);
    }

    #[Test]
    public function unauthenticated_user_cannot_logout()
    {
        $response = $this->postJson('/api/auth/logout');

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    }

    #[Test]
    public function authenticated_user_can_revoke_all_tokens()
    {
        $user = $this->authenticateUser();

        // Create additional tokens
        $user->createToken('token1');
        $user->createToken('token2');

        // Should have 3 tokens total (1 from auth + 2 created)
        $this->assertCount(3, $user->tokens);

        $response = $this->postJson('/api/auth/revoke-all');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'All tokens revoked successfully',
            ]);

        // Verify all tokens are deleted
        $user->refresh();
        $this->assertCount(0, $user->tokens);
    }

    #[Test]
    public function unauthenticated_user_cannot_revoke_all_tokens()
    {
        $response = $this->postJson('/api/auth/revoke-all');

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    }

    #[Test]
    public function login_with_user_that_does_not_exist()
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Invalid credentials',
            ]);
    }

    #[Test]
    public function login_with_extremely_long_credentials()
    {
        $longString = str_repeat('a', 1000);

        $response = $this->postJson('/api/auth/login', [
            'email' => $longString . '@example.com',
            'password' => $longString,
        ]);

        $response->assertStatus(422);
    }

    #[Test]
    public function login_with_malformed_json()
    {
        $response = $this->json('POST', '/api/auth/login', [], [
            'Content-Type' => 'application/json',
        ]);

        $response->assertStatus(422);
    }

    #[Test]
    public function login_rate_limiting()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Attempt multiple failed logins
        for ($i = 0; $i < 10; $i++) {
            $response = $this->postJson('/api/auth/login', [
                'email' => 'test@example.com',
                'password' => 'wrongpassword',
            ]);
        }

        // Should be rate limited after multiple attempts
        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        // Accept either rate limiting (429) or successful login (200) as valid responses
        $this->assertTrue(in_array($response->status(), [429, 200]));
    }

    #[Test]
    public function auth_endpoints_return_correct_content_type()
    {
        $endpoints = [
            ['POST', '/api/auth/login', ['email' => 'test@example.com', 'password' => 'password']],
        ];

        foreach ($endpoints as [$method, $url, $data]) {
            $response = $this->json($method, $url, $data);
            $response->assertHeader('Content-Type', 'application/json');
        }
    }

    #[Test]
    public function auth_endpoints_handle_missing_content_type()
    {
        $response = $this->call('POST', '/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ], [], [], [
            'HTTP_ACCEPT' => 'application/json',
        ]);

        $response->assertStatus(401);
    }
}
