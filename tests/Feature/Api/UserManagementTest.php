<?php

declare(strict_types=1);

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
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

    // Assign superadmin role to admin user if role system exists
    if (class_exists(Role::class)) {
        $adminRole = Role::firstOrCreate(['name' => Role::SUPERADMIN, 'guard_name' => 'web']);
        if (! $adminRole->isSuperAdminRole()) {
            $adminRole->forceFill(['is_super_admin' => true])->save();
        }
        $this->adminUser->assignRole($adminRole);
    }
});

test('authenticated user can list users', function () {
    $this->authenticateUser();
    User::factory(20)->create();

    $response = $this->getJson('/api/v1/users');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'message',
            'data' => ['*' => ['id', 'full_name', 'email']],
        ]);
});

test('unauthenticated user cannot list users', function () {
    $response = $this->getJson('/api/v1/users');

    $response->assertStatus(401)
        ->assertJson(['message' => 'Unauthenticated.']);
});

test('authenticated user can create user', function () {
    $this->authenticateAdmin();

    $userData = [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@example.com',
        'username' => 'johndoe',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ];

    $response = $this->postJson('/api/v1/users', $userData);

    $response->assertStatus(201);

    $this->assertDatabaseHas('users', [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@example.com',
        'username' => 'johndoe',
    ]);
});

test('user creation requires first name', function () {
    $this->authenticateAdmin();

    $response = $this->postJson('/api/v1/users', [
        'last_name' => 'Doe',
        'email' => 'john@example.com',
        'username' => 'johndoe',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertStatus(422)
        ->assertJsonPath('errors.first_name', ['The first name field is required.']);
});

test('user creation requires last name', function () {
    $this->authenticateAdmin();

    $response = $this->postJson('/api/v1/users', [
        'first_name' => 'John',
        'email' => 'john@example.com',
        'username' => 'johndoe',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertStatus(422)
        ->assertJsonPath('errors.last_name', ['The last name field is required.']);
});

test('user creation requires email', function () {
    $this->authenticateAdmin();

    $response = $this->postJson('/api/v1/users', [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'username' => 'johndoe',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertStatus(422)
        ->assertJsonPath('errors.email', ['The email field is required.']);
});

test('user creation requires unique email', function () {
    $this->authenticateAdmin();
    User::factory()->create(['email' => 'existing@example.com']);

    $response = $this->postJson('/api/v1/users', [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'existing@example.com',
        'username' => 'johndoe',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertStatus(422)
        ->assertJsonPath('errors.email', ['The email has already been taken.']);
});

test('user creation validates email format', function () {
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
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => $email,
            'username' => 'johndoe',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422);
    }
});

test('user creation requires password', function () {
    $this->authenticateAdmin();

    $response = $this->postJson('/api/v1/users', [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@example.com',
        'username' => 'johndoe',
    ]);

    $response->assertStatus(422)
        ->assertJsonPath('errors.password', ['The password field is required.']);
});

test('user creation requires password confirmation', function () {
    $this->authenticateAdmin();

    $response = $this->postJson('/api/v1/users', [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@example.com',
        'username' => 'johndoe',
        'password' => 'password123',
    ]);

    $response->assertStatus(422)
        ->assertJsonPath('errors.password', ['The password confirmation does not match.']);
});

test('user creation validates minimum password length', function () {
    $this->authenticateAdmin();

    $response = $this->postJson('/api/v1/users', [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@example.com',
        'username' => 'johndoe',
        'password' => '123',
        'password_confirmation' => '123',
    ]);

    $response->assertStatus(422)
        ->assertJsonPath('errors.password', ['The password must be at least 6 characters.']);
});

test('authenticated user can show user', function () {
    $this->authenticateUser();
    $user = User::factory()->create();

    $response = $this->getJson("/api/v1/users/{$user->id}");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'id',
                'full_name',
                'email',
            ],
        ]);
});

test('show user returns 404 for nonexistent user', function () {
    $this->authenticateUser();

    $response = $this->getJson('/api/v1/users/999999');

    $response->assertStatus(404);
});

test('authenticated user can update user', function () {
    $this->authenticateAdmin();
    $user = User::factory()->create();

    $updateData = [
        'first_name' => 'Updated',
        'last_name' => 'Name',
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
                'full_name',
                'email',
            ],
        ]);

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'first_name' => 'Updated',
        'last_name' => 'Name',
        'email' => 'updated@example.com',
        'username' => 'updated_username',
    ]);
});

test('user update validates unique email', function () {
    $this->authenticateAdmin();
    $user1 = User::factory()->create(['email' => 'user1@example.com']);
    $user2 = User::factory()->create(['email' => 'user2@example.com']);

    $response = $this->putJson("/api/v1/users/{$user1->id}", [
        'first_name' => $user1->first_name,
        'last_name' => $user1->last_name,
        'email' => 'user2@example.com',
        'username' => $user1->username,
    ]);

    $response->assertStatus(422)
        ->assertJsonPath('errors.email', ['The email has already been taken.']);
});

test('authenticated user can delete user', function () {
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
});

test('delete user returns 404 for nonexistent user', function () {
    $this->authenticateAdmin();

    $response = $this->deleteJson('/api/v1/users/999999');

    $response->assertStatus(404);
});

test('non-superadmin user cannot delete user', function () {
    $this->authenticateUser();
    $user = User::factory()->create();

    $response = $this->deleteJson("/api/v1/users/{$user->id}");

    $response->assertStatus(403);
});

test('authenticated user can bulk delete users', function () {
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
});

test('bulk delete requires ids array', function () {
    $this->authenticateAdmin();

    $response = $this->postJson('/api/v1/users/bulk-delete', []);

    $response->assertStatus(422)
        ->assertJsonPath('errors.ids', ['The ids field is required.']);
});

test('bulk delete validates ids are numeric', function () {
    $this->authenticateAdmin();

    $response = $this->postJson('/api/v1/users/bulk-delete', [
        'ids' => ['invalid', 'ids'],
    ]);

    $response->assertStatus(422);
});

test('non-superadmin user cannot bulk delete users', function () {
    $this->authenticateUser();
    $users = User::factory(3)->create();
    $userIds = $users->pluck('id')->toArray();

    $response = $this->postJson('/api/v1/users/bulk-delete', [
        'ids' => $userIds,
    ]);

    $response->assertStatus(403);
});

test('user management handles edge case inputs', function () {
    $this->authenticateAdmin();
    $edgeCases = $this->getEdgeCaseData();

    foreach ($edgeCases as $case => $value) {
        $response = $this->postJson('/api/v1/users', [
            'first_name' => $value,
            'last_name' => is_string($value) ? 'Doe' : 'Test',
            'email' => is_string($value) ? $value . '@example.com' : 'test@example.com',
            'username' => is_string($value) ? $value : 'testuser',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // Should handle gracefully (either success or validation error)
        expect([200, 201, 422])->toContain($response->status());
    }
});

test('user endpoints paginate results', function () {
    $this->authenticateUser();
    User::factory(50)->create();

    $response = $this->getJson('/api/v1/users');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'message',
            'data' => ['*' => ['id', 'full_name', 'email']],
        ]);
});

test('user endpoints accept pagination parameters', function () {
    $this->authenticateUser();
    User::factory(20)->create();

    $response = $this->getJson('/api/v1/users?page=2&per_page=5');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'message',
            'data' => ['*' => ['id', 'full_name', 'email']],
        ]);

    // Verify we get exactly 5 users (per_page parameter)
    $responseData = $response->json();
    expect($responseData['data'])->toHaveCount(5);
});

test('user creation with roles', function () {
    $this->authenticateAdmin();

    if (class_exists(Role::class)) {
        $role = Role::create(['name' => 'test-role']);

        $response = $this->postJson('/api/v1/users', [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane@example.com',
            'username' => 'janedoe',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'roles' => ['test-role'],
        ]);

        $response->assertStatus(201);
    } else {
        $this->markTestSkipped('Role system not implemented');
    }
});

test('user creation encrypts password', function () {
    $this->authenticateAdmin();

    $response = $this->postJson('/api/v1/users', [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@example.com',
        'username' => 'johndoe',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertStatus(201);

    $user = User::where('email', 'john@example.com')->first();
    $this->assertNotEquals('password123', $user->password);
    expect(Hash::check('password123', $user->password))->toBeTrue();
});

test('user update without password keeps existing password', function () {
    $this->authenticateAdmin();
    $user = User::factory()->create(['password' => Hash::make('original-password')]);
    $originalPassword = $user->password;

    $response = $this->putJson("/api/v1/users/{$user->id}", [
        'first_name' => $user->first_name,
        'last_name' => $user->last_name,
        'email' => $user->email,
        'username' => $user->username,
    ]);

    $response->assertStatus(200);

    $user->refresh();
    expect($user->password)->toEqual($originalPassword);
});

test('user endpoints handle sql injection attempts', function () {
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
});
