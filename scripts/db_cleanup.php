<?php
// Usage:
//   php scripts/db_cleanup.php --database=dbmanajemen --mode=preview
//   php scripts/db_cleanup.php --database=dbmanajemen --mode=drop
//
// This script boots Laravel, queries information_schema to find tables in the given database
// that are NOT in the whitelist, and optionally drops them (with FK checks disabled temporarily).

$root = dirname(__DIR__);
require $root . '/vendor/autoload.php';

$app = require $root . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Parse CLI options
$opts = getopt('', ['database:', 'mode::']);
$schema = $opts['database'] ?? env('DB_DATABASE');
$mode = strtolower($opts['mode'] ?? 'preview');
if (!$schema) {
    fwrite(STDERR, "Error: database name is required. Use --database=... or set DB_DATABASE in .env\n");
    exit(1);
}
if (!in_array($mode, ['preview', 'drop'], true)) {
    fwrite(STDERR, "Error: mode must be 'preview' or 'drop'\n");
    exit(1);
}

// Whitelist (tables that MUST be kept)
$keep = [
    'users','password_reset_tokens','sessions',
    'cache','cache_locks',
    'jobs','job_batches','failed_jobs',
    'products','stock_movements','notifications',
    'orders','order_items',
    'vehicles','assignments','deliveries',
    'carts','cart_items',
    'product_reviews',
    'migrations',
    'personal_access_tokens',
];

// Find candidate tables to drop
$candidates = collect(DB::select(
    "SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = ?",
    [$schema]
))->pluck('TABLE_NAME')
  ->reject(function($t) use ($keep) { return in_array($t, $keep, true); })
  ->values();

if ($candidates->isEmpty()) {
    echo "No tables to drop in schema '{$schema}'.\n";
    exit(0);
}

echo "Schema: {$schema}\n";
if ($mode === 'preview') {
    echo "Tables that WOULD be dropped (preview):\n";
    foreach ($candidates as $t) {
        echo " - {$t}\n";
    }
    echo "\nRun with --mode=drop to execute.\n";
    exit(0);
}

// mode === 'drop'
echo "Dropping " . $candidates->count() . " tables from schema '{$schema}'...\n";
DB::statement('SET FOREIGN_KEY_CHECKS=0');
foreach ($candidates as $t) {
    $sql = sprintf('DROP TABLE `%s`.`%s`', $schema, $t);
    try {
        DB::statement($sql);
        echo "Dropped: {$t}\n";
    } catch (Throwable $e) {
        fwrite(STDERR, "Failed to drop {$t}: " . $e->getMessage() . "\n");
    }
}
DB::statement('SET FOREIGN_KEY_CHECKS=1');

echo "Done.\n";
