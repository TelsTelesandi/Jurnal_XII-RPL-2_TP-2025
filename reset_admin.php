<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$u = App\Models\User::where('email', 'admin@gmail.com')->first();
if ($u) {
    echo "Found Admin: {$u->name}\n";
    $u->password = Illuminate\Support\Facades\Hash::make('password');
    $u->role_id = 1; // Make sure it's admin
    $u->save();
    echo "Password reset to 'password'\n";
} else {
    echo "Admin not found.";
}
