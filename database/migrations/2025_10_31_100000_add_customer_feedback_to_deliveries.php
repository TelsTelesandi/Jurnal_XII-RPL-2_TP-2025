<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('deliveries', function (Blueprint $table) {
            $table->unsignedTinyInteger('customer_rating')->nullable()->after('customer_notes');
            $table->string('customer_quality', 50)->nullable()->after('customer_rating');
        });
    }

    public function down(): void
    {
        Schema::table('deliveries', function (Blueprint $table) {
            $table->dropColumn(['customer_rating', 'customer_quality']);
        });
    }
};
