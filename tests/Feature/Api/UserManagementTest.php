<?php

namespace Tests\Feature\Api;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiTestUtils;
use Tests\TestCase;

class UserManagementTest extends TestCase
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
        if (class_exists(Role::class)) {
            $adminRole = Role::firstOrCreate(['name' => 'admin']);
            $this->adminUser->assignRole($adminRole);
        }
    }

    #[Test]
    public function authenticated_user_can_list_users()
    {
        $this->authenticateUser();
        User::factory(20)->create();

        $response = $this->getJson('/api/v1/users');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['*' => ['id', 'name', 'email']],
            ]);
    }

    #[Test]
    public function unauthenticated_user_cannot_list_users()
    {
        $response = $this->getJson('/api/v1/users');

        $response->assertStatus(401)
            ->assertJson(['message' => 'Unauthenticated.']);
    }

    #[Test]
    public function authenticated_user_can_create_user()
    {
        $this->authenticateAdmin();

        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'username' => 'johndoe',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/v1/users', $userData);

        $response->assertStatus(201);

        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'username' => 'johndoe',
        ]);
    }

    #[Test]
    public function user_creation_requires_name()
    {
        $this->authenticateAdmin();

        $response = $this->postJson('/api/v1/users', [
            'email' => 'john@example.com',
            'username' => 'johndoe',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('errors.name', ['The name field is required.']);
    }

    #[Test]
    public function user_creation_requires_email()
    {
        $this->authenticateAdmin();

        $response = $this->postJson('/api/v1/users', [
            'name' => 'John Doe',
            'username' => 'johndoe',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('errors.email', ['The email field is required.']);
    }

    #[Test]
    public function user_creation_requires_unique_email()
    {
        $this->authenticateAdmin();
        User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->postJson('/api/v1/users', [
            'name' => 'John Doe',
            'email' => 'existing@example.com',
            'username' => 'johndoe',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('errors.email', ['The email has already been taken.']);
    }

    #[Test]
    public function user_creation_validates_email_format()
    {
        $this->authenticateAdmin();

        $invalidEmails = [
            'not-email',
            '@domain.com',
            'test@',
            'test.domain.com',
            123,
            null,
        ];

        foreach ($invalidEmails as $email) {
            $response = $this->postJson('/api/v1/users', [
                'name' => 'John Doe',
                'email' => $email,
                'username' => 'johndoe',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

            $response->assertStatus(422);
        }
    }

    #[Test]
    public function user_creation_requires_password()
    {
        $this->authenticateAdmin();

        $response = $this->postJson('/api/v1/users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'username' => 'johndoe',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('errors.password', ['The password field is required.']);
    }

    #[Test]
    public function user_creation_requires_password_confirmation()
    {
        $this->authenticateAdmin();

        $response = $this->postJson('/api/v1/users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'username' => 'johndoe',
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('errors.password', ['The password confirmation does not match.']);
    }

    #[Test]
    public function user_creation_validates_minimum_password_length()
    {
        $this->authenticateAdmin();

        $response = $this->postJson('/api/v1/users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'username' => 'johndoe',
            'password' => '123',
            'password_confirmation' => '123',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('errors.password', ['The password must be at least 6 characters.']);
    }

    #[Test]
    public function authenticated_user_can_show_user()
    {
        $this->authenticateUser();
        $user = User::factory()->create();

        $response = $this->getJson("/api/v1/users/{$user->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'name',
                    'email',
                ],
            ]);
    }

    #[Test]
    public function show_user_returns_404_for_nonexistent_user()
    {
        $this->authenticateUser();

        $response = $this->getJson('/api/v1/users/999999');

        $response->assertStatus(404);
    }

    #[Test]
    public function authenticated_user_can_update_user()
    {
        $this->authenticateAdmin();
        $user = User::factory()->create();

        $updateData = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'username' => 'updated_username',
        ];

        $response = $this->putJson("/api/v1/users/{$user->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'name',
                    'email',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'username' => 'updated_username',
        ]);
    }

    #[Test]
    public function user_update_validates_unique_email()
    {
        $this->authenticateAdmin();
        $user1 = User::factory()->create(['email' => 'user1@example.com']);
        $user2 = User::factory()->create(['email' => 'user2@example.com']);

        $response = $this->putJson("/api/v1/users/{$user1->id}", [
            'name' => $user1->name,
            'email' => 'user2@example.com',
            'username' => $user1->username,
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('errors.email', ['The email has already been taken.']);
    }

    #[Test]
    public function authenticated_user_can_delete_user()
    {
        $this->authenticateAdmin();
        $user = User::factory()->create();

        $response = $this->deleteJson("/api/v1/users/{$user->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
            ]);

        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);
    }

    #[Test]
    public function delete_user_returns_404_for_nonexistent_user()
    {
        $this->authenticateAdmin();

        $response = $this->deleteJson('/api/v1/users/999999');

        $response->assertStatus(404);
    }

    #[Test]
    public function authenticated_user_can_bulk_delete_users()
    {
        $this->authenticateAdmin();
        $users = User::factory(3)->create();
        $userIds = $users->pluck('id')->toArray();

        $response = $this->postJson('/api/v1/users/bulk-delete', [
            'ids' => $userIds,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'deleted_count',
                ],
            ])
            ->assertJsonPath('data.deleted_count', 3);

        foreach ($userIds as $id) {
            $this->assertDatabaseMissing('users', ['id' => $id]);
        }
    }

    #[Test]
    public function bulk_delete_requires_ids_array()
    {
        $this->authenticateAdmin();

        $response = $this->postJson('/api/v1/users/bulk-delete', []);

        $response->assertStatus(422)
            ->assertJsonPath('errors.ids', ['The ids field is required.']);
    }

    #[Test]
    public function bulk_delete_validates_ids_are_numeric()
    {
        $this->authenticateAdmin();

        $response = $this->postJson('/api/v1/users/bulk-delete', [
            'ids' => ['invalid', 'ids'],
        ]);

        $response->assertStatus(422);
    }

    #[Test]
    public function user_management_handles_edge_case_inputs()
    {
        $this->authenticateAdmin();
        $edgeCases = $this->getEdgeCaseData();

        foreach ($edgeCases as $case => $value) {
            $response = $this->postJson('/api/v1/users', [
                'name' => $value,
                'email' => is_string($value) ? $value . '@example.com' : 'test@example.com',
                'username' => is_string($value) ? $value : 'testuser',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

            // Should handle gracefully (either success or validation error)
            $this->assertContains($response->status(), [200, 201, 422]);
        }
    }

    #[Test]
    public function user_endpoints_paginate_results()
    {
        $this->authenticateUser();
        User::factory(50)->create();

        $response = $this->getJson('/api/v1/users');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['*' => ['id', 'name', 'email']],
            ]);
    }

    #[Test]
    public function user_endpoints_accept_pagination_parameters()
    {
        $this->authenticateUser();
        User::factory(20)->create();

        $response = $this->getJson('/api/v1/users?page=2&per_page=5');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['*' => ['id', 'name', 'email']],
            ]);

        // Verify we get exactly 5 users (per_page parameter)
        $responseData = $response->json();
        $this->assertCount(5, $responseData['data']);
    }

    #[Test]
    public function user_creation_with_roles()
    {
        $this->authenticateAdmin();

        if (class_exists(Role::class)) {
            $role = Role::create(['name' => 'test-role']);

            $response = $this->postJson('/api/v1/users', [
                'name' => 'Jane Doe',
                'email' => 'jane@example.com',
                'username' => 'janedoe',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'roles' => [$role->id],
            ]);

            $response->assertStatus(201);
        } else {
            $this->markTestSkipped('Role system not implemented');
        }
    }

    #[Test]
    public function user_creation_encrypts_password()
    {
        $this->authenticateAdmin();

        $response = $this->postJson('/api/v1/users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'username' => 'johndoe',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201);

        $user = User::where('email', 'john@example.com')->first();
        $this->assertNotEquals('password123', $user->password);
        $this->assertTrue(Hash::check('password123', $user->password));
    }

    #[Test]
    public function user_update_without_password_keeps_existing_password()
    {
        $this->authenticateAdmin();
        $user = User::factory()->create(['password' => Hash::make('original-password')]);
        $originalPassword = $user->password;

        $response = $this->putJson("/api/v1/users/{$user->id}", [
            'name' => 'Updated Name',
            'email' => $user->email,
            'username' => $user->username,
        ]);

        $response->assertStatus(200);

        $user->refresh();
        $this->assertEquals($originalPassword, $user->password);
    }

    #[Test]
    public function user_endpoints_handle_sql_injection_attempts()
    {
        $this->authenticateUser();

        $maliciousInputs = [
            "'; DROP TABLE users; --",
            "1' OR '1'='1",
            "UNION SELECT * FROM users",
        ];

        foreach ($maliciousInputs as $input) {
            $response = $this->getJson("/api/v1/users?search={$input}");

            // Should not cause internal server error
            $this->assertNotEquals(500, $response->status());
        }
    }
}
