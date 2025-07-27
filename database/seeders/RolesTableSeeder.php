<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            'admin' => 'Administrator',
            'staff' => 'Staff',
            'social_media_manager' => 'Social Media Manager',
            'supplier' => 'Supplier',
            'tailor' => 'Tailor',
        ];

        foreach ($roles as $key => $roleName) {
            $role = Role::updateOrCreate([
                'name' => $roleName,
            ]);

            // Assign All Permissions to Administrator Role
            if ($key === 'admin') {
                $permissions = \Spatie\Permission\Models\Permission::all();
                $role->syncPermissions($permissions);
                $this->command->info("All permissions granted to Administrator role.");
            }
            
            // Assign specific permissions to Staff role
            elseif ($key === 'staff') {
                $staffPermissions = \Spatie\Permission\Models\Permission::whereIn('name', [
                    'View tasks list',
                    'View task',
                    'Update task status',
                    'View customers list',
                    'View customer',
                ])->get();
                $role->syncPermissions($staffPermissions);
                $this->command->info("Specific permissions granted to Staff role.");
            }
            
            // Assign specific permissions to Social Media Manager role
            elseif ($key === 'social_media_manager') {
                $socialPermissions = \Spatie\Permission\Models\Permission::whereIn('name', [
                    'View tasks list',
                    'View task',
                    'Create task',
                    'Update task',
                    'Update task status',
                    'View customers list',
                    'View customer',
                    'View task types list',
                    'View task type',
                ])->get();
                $role->syncPermissions($socialPermissions);
                $this->command->info("Specific permissions granted to Social Media Manager role.");
            }
            
            // Other roles get minimal permissions
            else {
                // Suppliers and Tailors get basic view permissions
                $basicPermissions = \Spatie\Permission\Models\Permission::whereIn('name', [
                    'View tasks list',
                    'View task',
                ])->get();
                $role->syncPermissions($basicPermissions);
                $this->command->info("Basic permissions granted to {$roleName} role.");
            }
        }
    }
}
