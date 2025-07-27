<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;

class GrantAllPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:grant-all {role=Administrator : The role to grant all permissions to}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Grant all permissions to a specific role (default: Administrator)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $roleName = $this->argument('role');
        
        $this->info("🚀 Granting all permissions to '{$roleName}' role...");

        // Get or create the role
        $role = Role::firstOrCreate(['name' => $roleName]);

        // Get all permissions
        $allPermissions = Permission::all();

        if ($allPermissions->isEmpty()) {
            $this->error('❌ No permissions found! Please run: php artisan db:seed --class=PermissionsTableSeeder');
            return 1;
        }

        // Grant all permissions to the role
        $role->syncPermissions($allPermissions);
        
        $this->info("✅ Granted {$allPermissions->count()} permissions to '{$roleName}' role");

        // Show permissions grouped by category
        $permissionsByGroup = $allPermissions->groupBy('group');
        foreach ($permissionsByGroup as $group => $permissions) {
            $this->line("  📁 {$group}: {$permissions->count()} permissions");
        }

        // Refresh permissions for all users with this role
        $users = User::role($roleName)->get();
        
        if ($users->isNotEmpty()) {
            $this->info("🔄 Refreshing permissions for {$users->count()} users with '{$roleName}' role:");
            foreach ($users as $user) {
                $user->syncPermissions([]);
                $this->line("  ✅ {$user->name} ({$user->email})");
            }
        }

        $this->info("🎉 All permissions successfully granted to '{$roleName}' role!");
        
        // Show verification command
        $this->line('');
        $this->comment('💡 To verify permissions, run: php artisan db:seed --class=VerifyPermissionsSeeder');
        
        return 0;
    }
}
