<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        User::insert([
            [
                'name' => 'Super Admin',
                'email' => 'superadmin@example.com',
                'username' => 'superadmin',
                'password' => Hash::make('12345678'),
                'phone' => '09906273148'
            ],
            [
                'name' => 'student',
                'email' => 'student@example.com',
                'username' => 'student',
                'password' => Hash::make('12345678'),
                'phone' => '09906273149'
            ],
            [
                'name' => 'teacher',
                'email' => 'teacher@example.com',
                'username' => 'teacher',
                'password' => Hash::make('12345678'),
                'phone' => '09906273140'
            ],
            [
                'name' => 'admin',
                'email' => 'admin@example.com',
                'username' => 'admin',
                'password' => Hash::make('12345678'),
                'phone' => '09906273141'
            ],
        ]);

        // Run factory to create additional users with unique details.
        User::factory()->count(500)->create();
        $this->command->info('Users table seeded with 502 users!');
    }
}
