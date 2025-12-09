<?php
// database/migrations/2025_09_04_000000_create_forum_message_poll.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('forum_message_reactions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('message_id')->constrained('forum_messages')->cascadeOnDelete();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('emoji', 32);
            $t->timestamps();
            $t->unique(['message_id', 'user_id', 'emoji']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('forum_message_reactions');
    }
};
