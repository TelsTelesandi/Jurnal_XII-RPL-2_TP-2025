<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Drop FK and column from assignments
        if (Schema::hasTable('assignments')) {
            Schema::table('assignments', function (Blueprint $table) {
                if (Schema::hasColumn('assignments', 'route_plan_id')) {
                    // Laravel helper to drop FK + column if constrained
                    try { $table->dropConstrainedForeignId('route_plan_id'); } catch (\Throwable $e) {
                        try { $table->dropForeign(['route_plan_id']); } catch (\Throwable $e2) {}
                        try { $table->dropColumn('route_plan_id'); } catch (\Throwable $e3) {}
                    }
                }
            });
        }

        // Drop tracking and planning tables
        Schema::dropIfExists('tracking_events');
        Schema::dropIfExists('order_tracking_events');
        Schema::dropIfExists('route_stops');
        Schema::dropIfExists('route_plans');
    }

    public function down(): void
    {
        // Not implemented (destructive). If needed, recreate tables manually.
    }
};
