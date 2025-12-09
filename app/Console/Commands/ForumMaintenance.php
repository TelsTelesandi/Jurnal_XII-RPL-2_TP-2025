<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;


namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ForumBan;
use App\Models\ForumMessage;
use Carbon\Carbon;

class ForumMaintenance extends Command
{
    protected $signature = 'forum:maintenance';
    protected $description = 'Maintenance forum: cleanup expired bans, old messages, etc.';

    public function handle()
    {
        $this->info('Starting forum maintenance...');

        // Cleanup expired bans
        $expiredBans = ForumBan::where('is_active', true)
            ->where('expires_at', '<', now())
            ->count();

        ForumBan::where('is_active', true)
            ->where('expires_at', '<', now())
            ->update(['is_active' => false]);

        $this->info("Deactivated {$expiredBans} expired bans");

        // Cleanup old deleted messages (older than 30 days)
        $oldMessages = ForumMessage::where('is_deleted', true)
            ->where('deleted_at', '<', now()->subDays(30))
            ->count();

        ForumMessage::where('is_deleted', true)
            ->where('deleted_at', '<', now()->subDays(30))
            ->forceDelete();

        $this->info("Permanently deleted {$oldMessages} old messages");

        // Cleanup orphaned attachments
        // Logic untuk hapus file attachment yang tidak terpakai
        // ...

        $this->info('Forum maintenance completed!');
    }
}