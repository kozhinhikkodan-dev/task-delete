<?php

namespace Database\Seeders;

use File;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use ReflectionClass;
use Spatie\Permission\Models\Permission;

class PermissionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $policyPath = app_path('Policies');
        $policyFiles = File::allFiles($policyPath);

        foreach ($policyFiles as $file) {
            $className = 'App\\Policies\\' . $file->getFilenameWithoutExtension();

            // Require file if not autoloaded
            if (!class_exists($className)) {
                require_once $file->getRealPath();
            }

            if (!class_exists($className)) {
                continue;
            }

            $reflection = new ReflectionClass($className);
            $resource = strtolower(str_replace('Policy', '', class_basename($className)));

            foreach ($reflection->getConstants() as $constantName => $constantValue) {
                if (str_starts_with($constantName, 'PERMISSION_')) {
                    Permission::firstOrCreate(['name' => $constantValue],
                        ['guard_name' => 'web', 'group' => $resource]
                    );
                }
            }
        }

    }
}
