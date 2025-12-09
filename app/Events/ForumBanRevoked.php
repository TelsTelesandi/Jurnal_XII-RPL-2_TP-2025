<?php
// app/Events/ForumBanRevoked.php
namespace App\Events;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class ForumBanRevoked implements ShouldBroadcastNow
{
    public int $userId;
    public function __construct(int $userId)
    {
        $this->userId = $userId;
    }
    public function broadcastOn(): array
    {
        return [new PrivateChannel('user.' . $this->userId)];
    }
    public function broadcastAs(): string
    {
        return 'forum.ban.revoked';
    }
}
