<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("
            ALTER TABLE `forum_messages`
            MODIFY `message_type` ENUM(
                'text','image','document','voice','contact','location','poll','video','announcement'
            ) NOT NULL DEFAULT 'text'
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE `forum_messages`
            MODIFY `message_type` ENUM(
                'text','image','document','voice','contact','location','poll','video'
            ) NOT NULL DEFAULT 'text'
        ");
    }
};
