<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Default Administrator User
        $defaultAdmin = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Administrator',
                'username' => 'admin',
                'password' => bcrypt('123456'),
                'status' => 'active'
            ]
        );
        if (!$defaultAdmin->hasRole('Administrator')) {
            $defaultAdmin->assignRole('Administrator');
        }

        // Test Admin User (existing)
        $user1 = User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test Admin',
                'password' => bcrypt('password'),
                'status' => 'active'
            ]
        );
        if (!$user1->hasRole('Administrator')) {
            $user1->assignRole('Administrator');
        }

        // Test Staff Users with task limits
        $staffUser1 = User::updateOrCreate(
            ['email' => 'staff1@example.com'],
            [
                'name' => 'Staff Member 1',
                'username' => 'staff1',
                'password' => bcrypt('password123'),
                'min_task_per_day' => 2,
                'max_task_per_day' => 2,
                'available_days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'],
                'status' => 'active'
            ]
        );
        if (!$staffUser1->hasRole('Staff')) {
            $staffUser1->assignRole('Staff');
        }

        $staffUser2 = User::updateOrCreate(
            ['email' => 'staff2@example.com'],
            [
                'name' => 'Staff Member 2',
                'username' => 'staff2',
                'password' => bcrypt('password123'),
                'min_task_per_day' => 1,
                'max_task_per_day' => 4,
                'available_days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
                'status' => 'active'
            ]
        );
        if (!$staffUser2->hasRole('Staff')) {
            $staffUser2->assignRole('Staff');
        }

        $staffUser3 = User::updateOrCreate(
            ['email' => 'staff3@example.com'],
            [
                'name' => 'Staff Member 3',
                'username' => 'staff3',
                'password' => bcrypt('password123'),
                'min_task_per_day' => 1,
                'max_task_per_day' => 4,
                'available_days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
                'status' => 'active'
            ]
        );
        if (!$staffUser3->hasRole('Staff')) {
            $staffUser3->assignRole('Staff');
        }

        $staffUser4 = User::updateOrCreate(
            ['email' => 'staff4@example.com'],
            [
                'name' => 'Staff Member 4',
                'username' => 'staff4',
                'password' => bcrypt('password123'),
                'min_task_per_day' => 1,
                'max_task_per_day' => 4,
                'available_days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
                'status' => 'active'
            ]
        );
        if (!$staffUser4->hasRole('Staff')) {
            $staffUser4->assignRole('Staff');
        }

        // Social Media Manager
        $socialManager = User::updateOrCreate(
            ['email' => 'social@example.com'],
            [
                'name' => 'Social Media Manager',
                'username' => 'social_manager',
                'password' => bcrypt('password123'),
                'status' => 'active'
            ]
        );
        if (!$socialManager->hasRole('Social Media Manager')) {
            $socialManager->assignRole('Social Media Manager');
        }

        // Test Supplier User (existing)
        $user2 = User::updateOrCreate(
            ['email' => 'supplier@example.com'],
            [
                'name' => 'Test Supplier',
                'password' => bcrypt('password123'),
                'status' => 'active'
            ]
        );
        if (!$user2->hasRole('Supplier')) {
            $user2->assignRole('Supplier');
        }

        // Test Tailor User (existing)
        $user3 = User::updateOrCreate(
            ['email' => 'tailor@example.com'],
            [
                'name' => 'Test Tailor',
                'password' => bcrypt('password123'),
                'status' => 'active'
            ]
        );
        if (!$user3->hasRole('Tailor')) {
            $user3->assignRole('Tailor');
        }
    }
}
