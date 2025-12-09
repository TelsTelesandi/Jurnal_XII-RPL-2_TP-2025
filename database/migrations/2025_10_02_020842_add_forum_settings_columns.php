<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('forum_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('forum_settings', 'allow_voice')) {
                $table->boolean('allow_voice')->default(false);
            }
            if (!Schema::hasColumn('forum_settings', 'max_attachment_kb')) {
                $table->integer('max_attachment_kb')->nullable();
            }
            if (!Schema::hasColumn('forum_settings', 'default_poll_duration_minutes')) {
                $table->integer('default_poll_duration_minutes')->nullable();
            }
            if (!Schema::hasColumn('forum_settings', 'max_users')) {
                $table->integer('max_users')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('forum_settings', function (Blueprint $table) {
            if (Schema::hasColumn('forum_settings', 'allow_voice')) {
                $table->dropColumn('allow_voice');
            }
            if (Schema::hasColumn('forum_settings', 'max_attachment_kb')) {
                $table->dropColumn('max_attachment_kb');
            }
            if (Schema::hasColumn('forum_settings', 'default_poll_duration_minutes')) {
                $table->dropColumn('default_poll_duration_minutes');
            }
            if (Schema::hasColumn('forum_settings', 'max_users')) {
                $table->dropColumn('max_users');
            }
        });
    }
};
