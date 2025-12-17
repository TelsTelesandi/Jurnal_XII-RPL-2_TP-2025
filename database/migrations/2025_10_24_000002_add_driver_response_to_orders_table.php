<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('driver_accepted_at')->nullable()->after('assigned_at');
            $table->timestamp('driver_declined_at')->nullable()->after('driver_accepted_at');
            $table->string('driver_decline_reason')->nullable()->after('driver_declined_at');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['driver_accepted_at','driver_declined_at','driver_decline_reason']);
        });
    }
};
