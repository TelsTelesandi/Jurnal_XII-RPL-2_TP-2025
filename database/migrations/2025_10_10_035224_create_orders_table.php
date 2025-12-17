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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('code')->unique();

            // Origin
            $table->string('origin_name')->nullable();
            $table->string('origin_address')->nullable();
            $table->decimal('origin_lat', 10, 7)->nullable();
            $table->decimal('origin_lng', 10, 7)->nullable();

            // Destination
            $table->string('destination_name')->nullable();
            $table->string('destination_address')->nullable();
            $table->decimal('destination_lat', 10, 7)->nullable();
            $table->decimal('destination_lng', 10, 7)->nullable();

            // Time windows
            $table->dateTime('window_from')->nullable();
            $table->dateTime('window_to')->nullable();

            // Payload
            $table->decimal('weight', 10, 2)->nullable();
            $table->decimal('volume', 10, 3)->nullable();

            // Status lifecycle: pending|planned|assigned|loading|in_transit|delivered|cancelled
            $table->string('status')->default('pending')->index();
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
