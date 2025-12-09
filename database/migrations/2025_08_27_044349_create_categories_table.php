<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        // Pastikan blog_posts sudah punya category_id
        Schema::table('blog_posts', function (Blueprint $table) {
            if (!Schema::hasColumn('blog_posts', 'category_id')) {
                $table->unsignedBigInteger('category_id')->nullable()->after('user_id');
                $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');

        Schema::table('blog_posts', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');
        });
    }
};
