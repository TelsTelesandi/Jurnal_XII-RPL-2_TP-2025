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
        Schema::create('route_stops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('route_plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('seq')->index();
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->dateTime('eta')->nullable();
            $table->dateTime('etd')->nullable();
            $table->dateTime('arrived_at')->nullable();
            $table->dateTime('departed_at')->nullable();
            // pending|skipped|done
            $table->string('status')->default('pending')->index();
            $table->decimal('distance_from_prev_km', 10, 2)->nullable();
            $table->integer('duration_from_prev_min')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('route_stops');
    }
};
