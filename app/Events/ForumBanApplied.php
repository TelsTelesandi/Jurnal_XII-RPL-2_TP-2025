<?php
// app/Events/ForumBanApplied.php
namespace App\Events;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

class ForumBanApplied implements ShouldBroadcastNow
{
    use SerializesModels;
    public int $userId;
    public array $ban;
    public function __construct(int $userId, array $ban)
    {
        $this->userId = $userId;
        $this->ban = $ban;
    }
    public function broadcastOn(): array
    {
        return [new PrivateChannel('user.' . $this->userId)];
    }
    public function broadcastAs(): string
    {
        return 'forum.ban.applied';
    }
}
