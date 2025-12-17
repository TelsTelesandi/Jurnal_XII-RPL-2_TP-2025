<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('driver_decline_admin_confirmed_at')->nullable()->after('driver_decline_reason');
            $table->unsignedBigInteger('driver_decline_admin_confirmed_by')->nullable()->after('driver_decline_admin_confirmed_at');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['driver_decline_admin_confirmed_at','driver_decline_admin_confirmed_by']);
        });
    }
};
