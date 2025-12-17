<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deliveries', function (Blueprint $table) {
            $table->text('driver_message')->nullable()->after('notes');
            $table->timestamp('customer_confirmed_at')->nullable()->after('driver_message');
            $table->string('customer_confirmation_status')->nullable()->after('customer_confirmed_at'); // approved|rejected
            $table->text('customer_notes')->nullable()->after('customer_confirmation_status');
        });
    }

    public function down(): void
    {
        Schema::table('deliveries', function (Blueprint $table) {
            $table->dropColumn(['driver_message','customer_confirmed_at','customer_confirmation_status','customer_notes']);
        });
    }
};
