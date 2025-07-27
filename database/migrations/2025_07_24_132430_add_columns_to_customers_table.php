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
        Schema::table('customers', function (Blueprint $table) {
            $table->decimal('total_posters', 10, 2)->nullable()->after('service_renew_date');
            $table->decimal('total_video_edits', 10, 2)->nullable()->after('total_posters');
            $table->decimal('total_blog_posts', 10, 2)->nullable()->after('total_video_edits');
            $table->decimal('total_anchoring_video', 10, 2)->nullable()->after('total_blog_posts');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_admin')->default(false)->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('total_posters');
            $table->dropColumn('total_video_edits');
            $table->dropColumn('total_blog_posts');
            $table->dropColumn('total_anchoring_video');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_admin');
        });
    }
};
