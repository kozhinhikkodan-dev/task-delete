<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;

class VerifyPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🔍 PERMISSION VERIFICATION REPORT');
        $this->command->line('==========================================');

        // Get all roles
        $roles = Role::with('permissions')->get();

        foreach ($roles as $role) {
            $this->command->info("📋 Role: {$role->name}");
            $this->command->line("   Permissions Count: {$role->permissions->count()}");
            
            if ($role->permissions->isNotEmpty()) {
                $permissionsByGroup = $role->permissions->groupBy('group');
                foreach ($permissionsByGroup as $group => $permissions) {
                    $this->command->line("   📁 {$group}: " . $permissions->pluck('name')->implode(', '));
                }
            } else {
                $this->command->warn("   ⚠️  No permissions assigned!");
            }
            
            // Show users with this role
            $users = User::role($role->name)->get();
            if ($users->isNotEmpty()) {
                $this->command->line("   👥 Users: " . $users->pluck('name')->implode(', '));
            }
            
            $this->command->line('');
        }

        // Summary
        $totalPermissions = Permission::count();
        $adminRole = Role::where('name', 'Administrator')->first();
        $adminPermissions = $adminRole ? $adminRole->permissions->count() : 0;
        
        $this->command->info('📊 SUMMARY');
        $this->command->line('==========================================');
        $this->command->info("Total Permissions in System: {$totalPermissions}");
        $this->command->info("Administrator Permissions: {$adminPermissions}");
        
        if ($adminPermissions === $totalPermissions) {
            $this->command->info("✅ Administrator has ALL permissions!");
        } else {
            $this->command->warn("⚠️  Administrator missing " . ($totalPermissions - $adminPermissions) . " permissions!");
        }

        // Verify Administrator users
        $adminUsers = User::role('Administrator')->get();
        $this->command->info("👑 Administrator Users: {$adminUsers->count()}");
        foreach ($adminUsers as $user) {
            $userPermissions = $user->getAllPermissions()->count();
            $this->command->line("   • {$user->name} ({$user->email}): {$userPermissions} permissions");
        }
    }
} 