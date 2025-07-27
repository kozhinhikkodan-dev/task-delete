<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->after('email', function ($table) {
                $table->string('username')->unique()->nullable();
            });
            
            $table->after('remember_token', function ($table) {
                $table->integer('min_task_per_day')->nullable();
                $table->integer('max_task_per_day')->nullable();
                $table->json('available_days')->nullable();
                $table->enum('status', ['active', 'inactive'])->default('active');
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'username',
                'min_task_per_day',
                'max_task_per_day',
                'available_days',
                'status'
            ]);
        });
    }
};
