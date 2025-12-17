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
        Schema::table('orders', function (Blueprint $table) {
            // Add ecommerce specific fields
            $table->decimal('total_amount', 10, 2)->after('notes');
            $table->string('customer_name')->after('customer_id');
            $table->string('customer_phone')->after('customer_name');
            $table->string('customer_email')->nullable()->after('customer_phone');
            $table->text('shipping_address')->after('customer_email');
            $table->string('payment_method')->default('cod')->after('shipping_address'); // cod, transfer, etc
            $table->string('payment_status')->default('pending')->after('payment_method'); // pending, paid, failed
            $table->timestamp('validated_at')->nullable()->after('payment_status');
            $table->foreignId('validated_by')->nullable()->constrained('users')->after('validated_at');
            $table->foreignId('assigned_driver_id')->nullable()->constrained('users')->after('validated_by');
            $table->timestamp('assigned_at')->nullable()->after('assigned_driver_id');
            
            // Update status enum for ecommerce workflow
            // pending -> validated -> assigned -> in_transit -> delivered -> cancelled
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'total_amount',
                'customer_name',
                'customer_phone', 
                'customer_email',
                'shipping_address',
                'payment_method',
                'payment_status',
                'validated_at',
                'validated_by',
                'assigned_driver_id',
                'assigned_at'
            ]);
        });
    }
};
