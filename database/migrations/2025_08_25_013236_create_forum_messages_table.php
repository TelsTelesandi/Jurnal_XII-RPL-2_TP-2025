<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('forum_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('message')->nullable();
            $table->string('file_path')->nullable(); // untuk gambar, dokumen, audio
            $table->enum('type', ['text', 'image', 'file', 'audio'])->default('text');
            $table->foreignId('reply_to_id')->nullable()->constrained('forum_messages')->nullOnDelete(); // untuk balasan
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('forum_messages');
    }
};
