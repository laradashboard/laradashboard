<?php

namespace Tests;

use App\Models\Role;
use App\Models\User;
use Spatie\Permission\Models\Permission;

trait ApiTestUtils
{
    protected User $user;
    protected User $adminUser;

    /**
     * Create basic roles for testing
     */
    protected function createRoles(): void
    {
        if (class_exists(Role::class)) {
            Role::firstOrCreate(['name' => 'admin']);
            Role::firstOrCreate(['name' => 'user']);
        }
    }

    /**
     * Create permissions for testing
     */
    protected function createPermissions(): void
    {
        if (class_exists(Permission::class)) {
            // User permissions
            Permission::firstOrCreate(['name' => 'user.view']);
            Permission::firstOrCreate(['name' => 'user.create']);
            Permission::firstOrCreate(['name' => 'user.edit']);
            Permission::firstOrCreate(['name' => 'user.delete']);

            // Post permissions
            Permission::firstOrCreate(['name' => 'post.view']);
            Permission::firstOrCreate(['name' => 'post.create']);
            Permission::firstOrCreate(['name' => 'post.edit']);
            Permission::firstOrCreate(['name' => 'post.delete']);

            // Role/Permission management permissions
            Permission::firstOrCreate(['name' => 'role.view']);
            Permission::firstOrCreate(['name' => 'role.create']);
            Permission::firstOrCreate(['name' => 'role.edit']);
            Permission::firstOrCreate(['name' => 'role.delete']);
            Permission::firstOrCreate(['name' => 'permission.view']);
        }
    }

    /**
     * Assign permissions to test users
     */
    protected function assignPermissions(): void
    {
        if (class_exists(Permission::class)) {
            $permissions = [
                'user.view',
                'user.create',
                'user.edit',
                'user.delete',
                'post.view',
                'post.create',
                'post.edit',
                'post.delete',
                'role.view',
                'role.create',
                'role.edit',
                'role.delete',
                'permission.view',
            ];

            // Give all permissions to both users
            $this->user->givePermissionTo($permissions);
            $this->adminUser->givePermissionTo($permissions);
        }
    }

    /**
     * Authenticate user with Sanctum
     */
    protected function authenticateUser(?User $user = null): User
    {
        $user = $user ?? $this->user;
        $token = $user->createToken('test-token')->plainTextToken;
        $this->withHeader('Authorization', 'Bearer ' . $token);
        return $user;
    }

    /**
     * Authenticate admin user with Sanctum
     */
    protected function authenticateAdmin(): User
    {
        $token = $this->adminUser->createToken('admin-test-token')->plainTextToken;
        $this->withHeader('Authorization', 'Bearer ' . $token);
        return $this->adminUser;
    }

    /**
     * Get common validation error response structure
     */
    protected function getValidationErrorStructure(): array
    {
        return [
            'message',
            'errors' => [],
        ];
    }

    /**
     * Get common API resource structure
     */
    protected function getApiResourceStructure(): array
    {
        return [
            'data',
            'links' => [
                'first',
                'last',
                'prev',
                'next',
            ],
            'meta' => [
                'current_page',
                'from',
                'last_page',
                'path',
                'per_page',
                'to',
                'total',
            ],
        ];
    }

    /**
     * Create test data with common edge cases
     */
    protected function getEdgeCaseData(): array
    {
        return [
            'empty_string' => '',
            'whitespace_string' => '   ',
            'null_value' => null,
            'very_long_string' => str_repeat('a', 300),
            'special_characters' => '<script>alert("xss")</script>',
            'sql_injection' => "'; DROP TABLE users; --",
            'unicode_characters' => '测试数据 🚀 émojis',
            'numeric_string' => '123456',
            'boolean_string' => 'true',
            'array_as_string' => '[1,2,3]',
            'json_string' => '{"key":"value"}',
            'negative_number' => -1,
            'zero' => 0,
            'large_number' => 999999999999,
        ];
    }
}
