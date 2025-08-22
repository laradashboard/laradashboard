<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use App\Services\PermissionService;
use App\Services\RolesService;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

/**
 * Class RolePermissionSeeder.
 *
 * @see https://spatie.be/docs/laravel-permission/v5/basic-usage/multiple-guards
 */
class RolePermissionSeeder extends Seeder
{
    public function __construct(
        private readonly PermissionService $permissionService,
        private readonly RolesService $rolesService
    ) {
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create all permissions
        $this->command->info('Creating permissions...');
        $this->permissionService->createPermissions();

        // Create predefined roles with their permissions
        $this->command->info('Creating predefined roles...');
        $roles = $this->rolesService->createPredefinedRoles();

        // Assign role to user if exists
        $user = User::where('username', 'superadmin')->first();
        if ($user) {
            $this->command->info('Assigning Superadmin role to superadmin user...');
            $user->assignRole($roles['superadmin']);
        }
        $teacher = User::where('username', 'teacher')->first();
        if ($teacher) {
            $this->command->info('Assigning teacher role to teacher user...');
            $teacher->assignRole($roles['teacher']);
        }
        $student = User::where('username', 'student')->first();
        if ($student) {
            $this->command->info('Assigning student role to student user...');
            $student->assignRole($roles['student']);
        }
        $admin = User::where('username', 'admin')->first();
        if ($admin) {
            $this->command->info('Assigning admin role to admin user...');
            $admin->assignRole($roles['admin']);
        }

        // Assign random roles to other users
        $this->command->info('Assigning random roles to other users...');
        $availableRoles = ['teacher', 'student']; // Exclude Superadmin from random assignment
        $users = User::all();

        foreach ($users as $user) {
            if (! $user->hasRole('Superadmin') and ! $user->hasRole('student') and ! $user->hasRole('teacher') and ! $user->hasRole('admin')) {
                // Get a random role from the available roles
                $randomRole = $availableRoles[array_rand($availableRoles)];
                $user->assignRole($randomRole);
            }
        }

        $this->command->info('Roles and Permissions created successfully!');
    }
}
