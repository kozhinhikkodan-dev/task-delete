<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $systemRoles = [
            'Administrator',
            // 'Staff',
            // 'Social Media Manager',
        ];

        foreach ($systemRoles as $roleName) {
            Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web',
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $systemRoles = [
            'Administrator',
            // 'Staff',
            // 'Social Media Manager',
        ];

        foreach ($systemRoles as $roleName) {
            Role::where('name', $roleName)->delete();
        }
    }
};
