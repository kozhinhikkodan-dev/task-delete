<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;

class GrantAllPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Starting to grant all permissions...');

        // Get or create Administrator role
        $adminRole = Role::firstOrCreate(['name' => 'Administrator']);

        // Get all permissions
        $allPermissions = Permission::all();

        if ($allPermissions->isEmpty()) {
            $this->command->warn('No permissions found. Please run PermissionsTableSeeder first.');
            return;
        }

        // Grant all permissions to Administrator role
        $adminPermissions = \Spatie\Permission\Models\Permission::whereNot('name','Show assigned tasks only')->get();
        $adminRole->syncPermissions($adminPermissions);
        
        $this->command->info("✅ Granted {$allPermissions->count()} permissions to Administrator role:");
        
        // Display granted permissions grouped by resource
        $permissionsByGroup = $allPermissions->groupBy('group');
        foreach ($permissionsByGroup as $group => $permissions) {
            $this->command->line("  📁 {$group}: " . $permissions->pluck('name')->implode(', '));
        }

        // Grant permissions to all existing Administrator users
        $adminUsers = User::role('Administrator')->get();
        
        if ($adminUsers->isNotEmpty()) {
            foreach ($adminUsers as $user) {
                // Clear existing permissions and sync with role permissions
                $user->syncPermissions([]);
                $this->command->info("✅ Refreshed permissions for Administrator user: {$user->name} ({$user->email})");
            }
        } else {
            $this->command->warn('No Administrator users found.');
        }

        $this->command->info('🎉 All permissions successfully granted to Administrator role!');
    }
} 