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
        Schema::create('route_plans', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->date('plan_date')->index();
            $table->decimal('total_distance_km', 10, 2)->default(0);
            $table->integer('total_duration_min')->default(0);
            // planned|assigned|in_progress|completed|cancelled
            $table->string('status')->default('planned')->index();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('route_plans');
    }
};
