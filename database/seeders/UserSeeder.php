<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superadmin = User::create([
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'email' => 'superadmin@example.com',
            'username' => 'superadmin',
            'password' => Hash::make('12345678'),
            'email_verified_at' => now(),
        ]);

        $admin = User::create([
            'first_name' => 'Admin',
            'last_name' => '',
            'email' => 'admin@example.com',
            'username' => 'admin',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $subscriber = User::create([
            'first_name' => 'Sub',
            'last_name' => 'Scriber',
            'email' => 'subscriber@example.com',
            'username' => 'subscriber',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        // Run factory to create additional users with unique details.
        User::factory()->count(500)->create();

        // Assign roles to core users (roles created in permission migration)
        $this->assignRolesToUsers($superadmin, $admin, $subscriber);

        $this->command->info('Users table seeded with 503 users!');
    }

    /**
     * Assign roles to core users.
     */
    private function assignRolesToUsers(User $superadmin, User $admin, User $subscriber): void
    {
        // Only assign if roles exist (created by migration)
        if (Role::where('name', 'Superadmin')->exists()) {
            $superadmin->assignRole('Superadmin');
            $this->command->info('Assigned Superadmin role to superadmin user.');
        }

        if (Role::where('name', 'Admin')->exists()) {
            $admin->assignRole('Admin');
            $this->command->info('Assigned Admin role to admin user.');
        }

        if (Role::where('name', 'Subscriber')->exists()) {
            $subscriber->assignRole('Subscriber');
            $this->command->info('Assigned Subscriber role to subscriber user.');
        }
    }
}
