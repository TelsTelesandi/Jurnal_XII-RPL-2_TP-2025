<?php

use App\Models\User;
use App\Models\ForumBan;

$users = User::all();
$count = 0;

foreach ($users as $u) {
    if ($u->getTotalReportsCount() >= 3 && !$u->isBannedFromForum()) {
        ForumBan::create([
            'user_id' => $u->id,
            'reason' => 'Auto-ban: Telah dilaporkan lebih dari 3 kali (Sistem).',
            'is_active' => true
        ]);
        echo "Banned: " . $u->name . PHP_EOL;
        $count++;
    }
}

echo "Total retro-banned: " . $count . PHP_EOL;
